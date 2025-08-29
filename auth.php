<?php
/**
 * 用户认证类
 */
class Auth {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * 用户登录
     */
    public function login($username, $password) {
        try {
            $stmt = $this->pdo->prepare("SELECT id, username, password, name, role, status FROM users WHERE username = ? AND status = 'active'");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // 设置会话
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                
                // 更新最后登录时间
                try {
                    $updateStmt = $this->pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $updateStmt->execute([$user['id']]);
                } catch (PDOException $updateError) {
                    error_log("更新登录时间错误: " . $updateError->getMessage());
                }
                
                // 记录登录日志
                $this->logAction($user['id'], 'login', '用户登录');
                
                return [
                    'success' => true,
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'name' => $user['name'],
                        'role' => $user['role']
                    ]
                ];
            } else {
                // 记录登录失败日志
                $this->logAction(null, 'login_failed', "用户名 {$username} 登录失败");
                
                return [
                    'success' => false,
                    'message' => '用户名或密码错误'
                ];
            }
        } catch (PDOException $e) {
            error_log("登录错误: " . $e->getMessage());
            return [
                'success' => false,
                'message' => '系统错误，请稍后重试'
            ];
        }
    }
    
    /**
     * 用户注销
     */
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $this->logAction($_SESSION['user_id'], 'logout', '用户注销');
        }
        
        session_destroy();
        return ['success' => true];
    }
    
    /**
     * 检查用户是否已登录
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * 获取当前用户信息
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        try {
            $stmt = $this->pdo->prepare("SELECT id, username, name, email, role, status FROM users WHERE id = ? AND status = 'active'");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("获取用户信息错误: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * 检查用户权限
     */
    public function hasPermission($permission) {
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }
        
        switch ($permission) {
            case 'admin':
                return $user['role'] === 'admin';
            case 'user':
                return $user['role'] === 'admin' || $user['role'] === 'user';
            default:
                return false;
        }
    }
    
    /**
     * 修改密码
     */
    public function changePassword($oldPassword, $newPassword) {
        if (!$this->isLoggedIn()) {
            return ['success' => false, 'message' => '请先登录'];
        }
        
        try {
            $stmt = $this->pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($oldPassword, $user['password'])) {
                return ['success' => false, 'message' => '原密码错误'];
            }
            
            $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([
                password_hash($newPassword, PASSWORD_DEFAULT),
                $_SESSION['user_id']
            ]);
            
            $this->logAction($_SESSION['user_id'], 'change_password', '修改密码');
            
            return ['success' => true, 'message' => '密码修改成功'];
        } catch (PDOException $e) {
            error_log("修改密码错误: " . $e->getMessage());
            return ['success' => false, 'message' => '系统错误，请稍后重试'];
        }
    }
    
    /**
     * 创建用户（仅管理员）
     */
    public function createUser($username, $password, $name, $email = '', $role = 'user') {
        if (!$this->hasPermission('admin')) {
            return ['success' => false, 'message' => '权限不足'];
        }
        
        try {
            // 检查用户名是否已存在
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->fetchColumn() > 0) {
                return ['success' => false, 'message' => '用户名已存在'];
            }
            
            $stmt = $this->pdo->prepare("INSERT INTO users (username, password, name, email, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $username,
                password_hash($password, PASSWORD_DEFAULT),
                $name,
                $email,
                $role
            ]);
            
            $this->logAction($_SESSION['user_id'], 'create_user', "创建用户: {$username}");
            
            return ['success' => true, 'message' => '用户创建成功'];
        } catch (PDOException $e) {
            error_log("创建用户错误: " . $e->getMessage());
            return ['success' => false, 'message' => '系统错误，请稍后重试'];
        }
    }
    
    /**
     * 获取用户列表（仅管理员）
     */
    public function getUserList() {
        if (!$this->hasPermission('admin')) {
            return ['success' => false, 'message' => '权限不足'];
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, username, name, role, status, created_at, last_login 
                FROM users 
                ORDER BY created_at DESC
            ");
            $stmt->execute();
            $users = $stmt->fetchAll();
            
            return ['success' => true, 'users' => $users];
        } catch (PDOException $e) {
            error_log("获取用户列表错误: " . $e->getMessage());
            return ['success' => false, 'message' => '系统错误，请稍后重试'];
        }
    }
    
    /**
     * 记录管理操作日志
     */
    public function logOperation($action, $description) {
        if ($this->isLoggedIn()) {
            $this->logAction($_SESSION['user_id'], $action, $description);
        }
    }
    
    /**
     * 记录操作日志
     */
    private function logAction($userId, $action, $description) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO logs (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $userId,
                $action,
                $description,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        } catch (PDOException $e) {
            error_log("记录日志错误: " . $e->getMessage());
        }
    }
}
?>
