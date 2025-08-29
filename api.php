<?php
// 严格的输出控制
ob_start();

// 关闭所有可能的输出
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// 设置响应头
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 清除任何之前的输出
if (ob_get_level()) {
    ob_clean();
}

try {
    require_once 'config.php';
    require_once 'auth.php';
} catch (Exception $e) {
    // 清除输出缓冲区
    if (ob_get_level()) {
        ob_clean();
    }
    echo json_encode([
        'success' => false,
        'message' => '系统配置错误: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$auth = new Auth($pdo);
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// 不需要登录验证的接口
$publicActions = ['login', 'check_login'];

// 检查登录状态（除了公开接口）
if (!in_array($action, $publicActions) && !$auth->isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => '请先登录',
        'need_login' => true
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

switch ($action) {
    case 'login':
        handleLogin($auth);
        break;
    
    case 'logout':
        handleLogout($auth);
        break;
    
    case 'check_login':
        checkLogin($auth);
        break;
    
    case 'change_password':
        changePassword($auth);
        break;
    
    case 'get_user_info':
        getUserInfo($auth);
        break;
    
    case 'get_users':
        getUsers($auth);
        break;
    
    case 'create_user':
        createUser($auth);
        break;
    
    case 'add_user':
        addUser($auth, $pdo);
        break;
    
    case 'update_user':
        updateUser($auth, $pdo);
        break;
    
    case 'delete_user':
        deleteUser($auth, $pdo);
        break;
    
    case 'reset_user_password':
        resetUserPassword($auth, $pdo);
        break;
    
    case 'get_all_reports':
        getAllReports($auth, $pdo);
        break;
    
    case 'get_report':
        getReport($auth, $pdo);
        break;
    
    case 'save_report':
        saveReport($auth, $pdo);
        break;
    
    case 'delete_report':
        deleteReport($auth, $pdo);
        break;
    
    default:
        echo json_encode([
            'success' => false,
            'message' => '无效的操作'
        ], JSON_UNESCAPED_UNICODE);
        break;
}

/**
 * 处理用户登录
 */
function handleLogin($auth) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => '用户名和密码不能为空'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    $result = $auth->login($username, $password);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}

/**
 * 处理用户注销
 */
function handleLogout($auth) {
    $result = $auth->logout();
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}

/**
 * 检查登录状态
 */
function checkLogin($auth) {
    if ($auth->isLoggedIn()) {
        $user = $auth->getCurrentUser();
        echo json_encode([
            'success' => true,
            'logged_in' => true,
            'user' => $user
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => true,
            'logged_in' => false
        ], JSON_UNESCAPED_UNICODE);
    }
}

/**
 * 修改密码
 */
function changePassword($auth) {
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    
    if (empty($oldPassword) || empty($newPassword)) {
        echo json_encode([
            'success' => false,
            'message' => '原密码和新密码不能为空'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    if (strlen($newPassword) < 6) {
        echo json_encode([
            'success' => false,
            'message' => '新密码长度不能少于6位'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    $result = $auth->changePassword($oldPassword, $newPassword);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}

/**
 * 获取用户信息
 */
function getUserInfo($auth) {
    $user = $auth->getCurrentUser();
    if ($user) {
        echo json_encode([
            'success' => true,
            'user' => $user
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'message' => '获取用户信息失败'
        ], JSON_UNESCAPED_UNICODE);
    }
}

/**
 * 获取用户列表（仅管理员）
 */
function getUsers($auth) {
    $result = $auth->getUserList();
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}

/**
 * 创建用户（仅管理员）
 */
function createUser($auth) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? 'user';
    
    if (empty($username) || empty($password) || empty($name)) {
        echo json_encode([
            'success' => false,
            'message' => '用户名、密码和姓名不能为空'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    $result = $auth->createUser($username, $password, $name, $email, $role);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}

/**
 * 添加用户（新版本，使用PDO）
 */
function addUser($auth, $pdo) {
    // 检查管理员权限
    $user = $auth->getCurrentUser();
    if ($user['role'] !== 'admin') {
        echo json_encode([
            'success' => false,
            'message' => '权限不足'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $name = $_POST['name'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $status = $_POST['status'] ?? 'active';
    
    if (empty($username) || empty($password) || empty($name)) {
        echo json_encode([
            'success' => false,
            'message' => '用户名、密码和姓名不能为空'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    if (strlen($password) < 6) {
        echo json_encode([
            'success' => false,
            'message' => '密码长度不能少于6位'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    try {
        // 检查用户名是否已存在
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode([
                'success' => false,
                'message' => '用户名已存在'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // 创建用户
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (username, password, name, role, status, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $result = $stmt->execute([$username, $hashedPassword, $name, $role, $status]);
        
        if ($result) {
            // 记录操作日志
            $auth->logOperation('add_user', "添加用户: $username ($name)");
            
            echo json_encode([
                'success' => true,
                'message' => '用户添加成功'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false,
                'message' => '用户添加失败'
            ], JSON_UNESCAPED_UNICODE);
        }
        
    } catch (PDOException $e) {
        error_log("添加用户错误: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => '添加用户失败'
        ], JSON_UNESCAPED_UNICODE);
    }
}

/**
 * 更新用户信息
 */
function updateUser($auth, $pdo) {
    // 检查管理员权限
    $user = $auth->getCurrentUser();
    if ($user['role'] !== 'admin') {
        echo json_encode([
            'success' => false,
            'message' => '权限不足'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    $userId = $_POST['user_id'] ?? '';
    $username = $_POST['username'] ?? '';
    $name = $_POST['name'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $status = $_POST['status'] ?? 'active';
    
    if (empty($userId) || empty($username) || empty($name)) {
        echo json_encode([
            'success' => false,
            'message' => '用户ID、用户名和姓名不能为空'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    try {
        // 检查用户是否存在
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        if (!$stmt->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => '用户不存在'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // 检查用户名是否被其他用户使用
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $userId]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode([
                'success' => false,
                'message' => '用户名已被其他用户使用'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // 更新用户信息
        $stmt = $pdo->prepare("
            UPDATE users 
            SET username = ?, name = ?, role = ?, status = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $result = $stmt->execute([$username, $name, $role, $status, $userId]);
        
        if ($result) {
            // 记录操作日志
            $auth->logOperation('update_user', "更新用户: $username ($name)");
            
            echo json_encode([
                'success' => true,
                'message' => '用户更新成功'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false,
                'message' => '用户更新失败'
            ], JSON_UNESCAPED_UNICODE);
        }
        
    } catch (PDOException $e) {
        error_log("更新用户错误: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => '更新用户失败'
        ], JSON_UNESCAPED_UNICODE);
    }
}

/**
 * 删除用户
 */
function deleteUser($auth, $pdo) {
    // 检查管理员权限
    $user = $auth->getCurrentUser();
    if ($user['role'] !== 'admin') {
        echo json_encode([
            'success' => false,
            'message' => '权限不足'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    $userId = $_POST['user_id'] ?? '';
    
    if (empty($userId)) {
        echo json_encode([
            'success' => false,
            'message' => '用户ID不能为空'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    // 不允许删除ID为1的管理员账户
    if ($userId == 1) {
        echo json_encode([
            'success' => false,
            'message' => '不能删除默认管理员账户'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    // 不允许删除自己
    if ($userId == $user['id']) {
        echo json_encode([
            'success' => false,
            'message' => '不能删除自己的账户'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    try {
        // 获取用户信息用于日志
        $stmt = $pdo->prepare("SELECT username, name FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $targetUser = $stmt->fetch();
        
        if (!$targetUser) {
            echo json_encode([
                'success' => false,
                'message' => '用户不存在'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // 开始事务
        $pdo->beginTransaction();
        
        try {
            // 删除用户的所有报告
            $stmt = $pdo->prepare("DELETE FROM reports WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            // 删除用户记录
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $result = $stmt->execute([$userId]);
            
            if ($result && $stmt->rowCount() > 0) {
                // 提交事务
                $pdo->commit();
                
                // 记录操作日志
                $auth->logOperation('delete_user', "删除用户: {$targetUser['username']} ({$targetUser['name']})");
                
                echo json_encode([
                    'success' => true,
                    'message' => '用户删除成功'
                ], JSON_UNESCAPED_UNICODE);
            } else {
                $pdo->rollback();
                echo json_encode([
                    'success' => false,
                    'message' => '用户删除失败'
                ], JSON_UNESCAPED_UNICODE);
            }
        } catch (Exception $e) {
            $pdo->rollback();
            throw $e;
        }
        
    } catch (PDOException $e) {
        error_log("删除用户错误: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => '删除用户失败'
        ], JSON_UNESCAPED_UNICODE);
    }
}

/**
 * 重置用户密码
 */
function resetUserPassword($auth, $pdo) {
    // 检查管理员权限
    $user = $auth->getCurrentUser();
    if ($user['role'] !== 'admin') {
        echo json_encode([
            'success' => false,
            'message' => '权限不足'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    $userId = $_POST['user_id'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    
    if (empty($userId) || empty($newPassword)) {
        echo json_encode([
            'success' => false,
            'message' => '用户ID和新密码不能为空'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    if (strlen($newPassword) < 6) {
        echo json_encode([
            'success' => false,
            'message' => '密码长度不能少于6位'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    try {
        // 获取用户信息用于日志
        $stmt = $pdo->prepare("SELECT username, name FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $targetUser = $stmt->fetch();
        
        if (!$targetUser) {
            echo json_encode([
                'success' => false,
                'message' => '用户不存在'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // 更新密码
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
        $result = $stmt->execute([$hashedPassword, $userId]);
        
        if ($result) {
            // 记录操作日志
            $auth->logOperation('reset_password', "重置用户密码: {$targetUser['username']} ({$targetUser['name']})");
            
            echo json_encode([
                'success' => true,
                'message' => '密码重置成功'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false,
                'message' => '密码重置失败'
            ], JSON_UNESCAPED_UNICODE);
        }
        
    } catch (PDOException $e) {
        error_log("重置密码错误: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => '重置密码失败'
        ], JSON_UNESCAPED_UNICODE);
    }
}

/**
 * 获取所有报告
 */
function getAllReports($auth, $pdo) {
    try {
        $user = $auth->getCurrentUser();
        $userId = $user['id'];
        
        // 管理员可以查看所有报告，普通用户只能查看自己的报告
        if ($user['role'] === 'admin') {
            $stmt = $pdo->prepare("
                SELECT r.*, u.name as reporter_name 
                FROM reports r 
                LEFT JOIN users u ON r.user_id = u.id 
                ORDER BY r.report_date DESC
            ");
            $stmt->execute();
        } else {
            $stmt = $pdo->prepare("
                SELECT r.*, u.name as reporter_name 
                FROM reports r 
                LEFT JOIN users u ON r.user_id = u.id 
                WHERE r.user_id = ? 
                ORDER BY r.report_date DESC
            ");
            $stmt->execute([$userId]);
        }
        
        $reports = $stmt->fetchAll();
        
        // 转换为前端需要的格式
        $formattedReports = [];
        foreach ($reports as $report) {
            $formattedReports[$report['report_date']] = [
                'date' => $report['report_date'],
                'reporter_name' => $report['reporter_name'],
                'work_content' => $report['work_content'],
                'next_plan' => $report['next_plan'],
                'issues' => $report['issues'],
                'suggestions' => $report['suggestions'],
                'created_at' => $report['created_at'],
                'updated_at' => $report['updated_at']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'reports' => $formattedReports
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        error_log("获取报告列表错误: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => '获取报告列表失败'
        ], JSON_UNESCAPED_UNICODE);
    }
}

/**
 * 获取单个报告
 */
function getReport($auth, $pdo) {
    $date = $_GET['date'] ?? '';
    
    if (empty($date)) {
        echo json_encode([
            'success' => false,
            'message' => '日期参数不能为空'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    try {
        $user = $auth->getCurrentUser();
        
        if ($user['role'] === 'admin') {
            $stmt = $pdo->prepare("
                SELECT r.*, u.name as reporter_name 
                FROM reports r 
                LEFT JOIN users u ON r.user_id = u.id 
                WHERE r.report_date = ?
            ");
            $stmt->execute([$date]);
        } else {
            $stmt = $pdo->prepare("
                SELECT r.*, u.name as reporter_name 
                FROM reports r 
                LEFT JOIN users u ON r.user_id = u.id 
                WHERE r.report_date = ? AND r.user_id = ?
            ");
            $stmt->execute([$date, $user['id']]);
        }
        
        $report = $stmt->fetch();
        
        if ($report) {
            echo json_encode([
                'success' => true,
                'report' => [
                    'date' => $report['report_date'],
                    'reporter_name' => $report['reporter_name'],
                    'work_content' => $report['work_content'],
                    'next_plan' => $report['next_plan'],
                    'issues' => $report['issues'],
                    'suggestions' => $report['suggestions'],
                    'created_at' => $report['created_at'],
                    'updated_at' => $report['updated_at']
                ]
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false,
                'message' => '未找到该日期的报告'
            ], JSON_UNESCAPED_UNICODE);
        }
        
    } catch (PDOException $e) {
        error_log("获取报告错误: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => '获取报告失败'
        ], JSON_UNESCAPED_UNICODE);
    }
}

/**
 * 保存报告
 */
function saveReport($auth, $pdo) {
    error_log("saveReport called at " . date('Y-m-d H:i:s'));
    error_log("POST data: " . print_r($_POST, true));
    
    $selected_date = $_POST['selected_date'] ?? '';
    $work_content = $_POST['work_content'] ?? '';
    $next_plan = $_POST['next_plan'] ?? '';
    $issues = $_POST['issues'] ?? '';
    $suggestions = $_POST['suggestions'] ?? '';
    
    if (empty($selected_date)) {
        error_log("Error: 日期不能为空");
        echo json_encode([
            'success' => false,
            'message' => '日期不能为空'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    // 验证日期格式
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selected_date)) {
        error_log("Error: 日期格式错误: $selected_date");
        echo json_encode([
            'success' => false,
            'message' => '日期格式错误'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    try {
        $user = $auth->getCurrentUser();
        $userId = $user['id'];
        
        // 检查是否已存在该日期的报告
        $stmt = $pdo->prepare("SELECT id FROM reports WHERE user_id = ? AND report_date = ?");
        $stmt->execute([$userId, $selected_date]);
        $existingReport = $stmt->fetch();
        
        if ($existingReport) {
            // 更新现有报告
            $stmt = $pdo->prepare("
                UPDATE reports 
                SET work_content = ?, next_plan = ?, issues = ?, suggestions = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE user_id = ? AND report_date = ?
            ");
            $result = $stmt->execute([$work_content, $next_plan, $issues, $suggestions, $userId, $selected_date]);
        } else {
            // 创建新报告
            $stmt = $pdo->prepare("
                INSERT INTO reports (user_id, report_date, work_content, next_plan, issues, suggestions) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $result = $stmt->execute([$userId, $selected_date, $work_content, $next_plan, $issues, $suggestions]);
        }
        
        if ($result) {
            error_log("Save successful");
            echo json_encode([
                'success' => true,
                'message' => '周报保存成功'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            error_log("Save failed!");
            echo json_encode([
                'success' => false,
                'message' => '保存失败'
            ], JSON_UNESCAPED_UNICODE);
        }
        
    } catch (PDOException $e) {
        error_log("保存报告错误: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => '保存失败：' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
}

/**
 * 删除报告
 */
function deleteReport($auth, $pdo) {
    $date = $_POST['date'] ?? '';
    
    if (empty($date)) {
        echo json_encode([
            'success' => false,
            'message' => '日期参数不能为空'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    try {
        $user = $auth->getCurrentUser();
        
        if ($user['role'] === 'admin') {
            // 管理员可以删除任何报告
            $stmt = $pdo->prepare("DELETE FROM reports WHERE report_date = ?");
            $result = $stmt->execute([$date]);
        } else {
            // 普通用户只能删除自己的报告
            $stmt = $pdo->prepare("DELETE FROM reports WHERE report_date = ? AND user_id = ?");
            $result = $stmt->execute([$date, $user['id']]);
        }
        
        if ($result && $stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => '报告删除成功'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false,
                'message' => '未找到该日期的报告或无权限删除'
            ], JSON_UNESCAPED_UNICODE);
        }
        
    } catch (PDOException $e) {
        error_log("删除报告错误: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => '删除失败'
        ], JSON_UNESCAPED_UNICODE);
    }
}

// 刷新输出缓冲区并结束
if (ob_get_level()) {
    ob_end_flush();
}
?>
