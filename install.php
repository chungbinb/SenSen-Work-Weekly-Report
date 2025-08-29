<?php
/**
 * 工作周报表系统 - 数据库安装脚本
 * 版本: 2.0
 */

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 处理AJAX请求（必须在任何输出之前）
if (isset($_GET['action']) && $_GET['action'] === 'test_db') {
    header('Content-Type: application/json');
    
    try {
        $config = [
            'host' => $_POST['db_host'] ?? '',
            'port' => $_POST['db_port'] ?? '3306',
            'dbname' => $_POST['db_name'] ?? '',
            'username' => $_POST['db_user'] ?? '',
         {} catch (PDOException \$e) {
    error_log(\"数据库连接失败: \" . \$e->getMessage());
    // 不使用die()，而是抛出异常让调用方处理
    throw new Exception(\"数据库连接失败: \" . \$e->getMessage());
}password' => $_POST['db_pass'] ?? ''
        ];
        
        if (empty($config['host']) || empty($config['dbname']) || empty($config['username'])) {
            throw new Exception('请填写完整的数据库连接信息');
        }
        
        $pdo = createDatabaseConnection($config);
        echo json_encode(['success' => true, 'message' => '连接成功'], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// 检查是否已经安装
if (file_exists('./install.lock')) {
    die('
    <html>
    <head>
        <meta charset="UTF-8">
        <title>系统已安装</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            .alert { padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; color: #721c24; }
        </style>
    </head>
    <body>
        <div class="alert">
            <h3>系统已经安装完成！</h3>
            <p>如需重新安装，请删除 install.lock 文件。</p>
            <p><a href="index.php">进入系统</a></p>
        </div>
    </body>
    </html>
    ');
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>工作周报表系统 - 安装向导</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'PingFang SC', sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container { 
            max-width: 600px; 
            margin: 40px auto; 
            background: white; 
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            padding: 30px; 
            text-align: center; 
        }
        .content { padding: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #333; }
        .form-group input { 
            width: 100%; 
            padding: 12px; 
            border: 2px solid #e9ecef; 
            border-radius: 8px; 
            font-size: 14px;
        }
        .form-group input:focus { 
            outline: none; 
            border-color: #667eea; 
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            border: none; 
            padding: 12px 24px; 
            border-radius: 8px; 
            cursor: pointer; 
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; }
        .alert { 
            padding: 15px; 
            margin-bottom: 20px; 
            border-radius: 8px; 
            border: 1px solid transparent; 
        }
        .alert-success { color: #155724; background: #d4edda; border-color: #c3e6cb; }
        .alert-error { color: #721c24; background: #f8d7da; border-color: #f5c6cb; }
        .alert-info { color: #0c5460; background: #d1ecf1; border-color: #bee5eb; }
        .step-indicator { 
            display: flex; 
            justify-content: center; 
            margin-bottom: 30px; 
        }
        .step { 
            width: 40px; 
            height: 40px; 
            border-radius: 50%; 
            background: #e9ecef; 
            color: #6c757d; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin: 0 10px; 
            font-weight: bold;
        }
        .step.active { background: #667eea; color: white; }
        .step.completed { background: #28a745; color: white; }
        .progress { 
            background: #e9ecef; 
            height: 20px; 
            border-radius: 10px; 
            overflow: hidden; 
            margin: 20px 0;
        }
        .progress-bar { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            height: 100%; 
            border-radius: 10px; 
            transition: width 0.3s ease;
        }
        .btn-group { 
            display: flex; 
            gap: 15px; 
            justify-content: center; 
            margin-top: 30px; 
        }
        ul { margin-left: 20px; }
        li { margin-bottom: 5px; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📅 工作周报表系统</h1>
            <p>数据库安装向导</p>
        </div>
        
        <div class="content">
            <div class="step-indicator">
                <div class="step <?php echo $step >= 1 ? 'active' : ''; ?>">1</div>
                <div class="step <?php echo $step >= 2 ? 'active' : ''; ?>">2</div>
                <div class="step <?php echo $step >= 3 ? 'active' : ''; ?>">3</div>
            </div>
            
            <?php
            switch ($step) {
                case 1:
                    showWelcome();
                    break;
                case 2:
                    showDatabaseConfig();
                    break;
                case 3:
                    performInstallation();
                    break;
                default:
                    showWelcome();
            }
            ?>
        </div>
    </div>
    
    <script>
        function nextStep() {
            const currentStep = <?php echo $step; ?>;
            window.location.href = 'install.php?step=' + (currentStep + 1);
        }
        
        function testDatabase() {
            const formData = new FormData(document.getElementById('dbForm'));
            const resultDiv = document.getElementById('db-test-result');
            
            resultDiv.innerHTML = '<div class="alert alert-info">正在测试数据库连接...</div>';
            
            fetch('install.php?action=test_db', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                console.log('Response text:', text); // 调试输出
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        resultDiv.innerHTML = '<div class="alert alert-success">数据库连接测试成功！</div>';
                        document.getElementById('installBtn').disabled = false;
                    } else {
                        resultDiv.innerHTML = '<div class="alert alert-error">连接失败: ' + data.message + '</div>';
                        document.getElementById('installBtn').disabled = true;
                    }
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    console.error('Response text:', text);
                    resultDiv.innerHTML = '<div class="alert alert-error">服务器响应格式错误。请检查PHP配置和错误日志。<br><small>响应内容: ' + text.substring(0, 200) + '</small></div>';
                    document.getElementById('installBtn').disabled = true;
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                resultDiv.innerHTML = '<div class="alert alert-error">网络请求失败: ' + error.message + '</div>';
                document.getElementById('installBtn').disabled = true;
            });
        }
    </script>
</body>
</html>

<?php

function showWelcome() {
    ?>
    <h2>欢迎使用工作周报表系统</h2>
    <div class="alert alert-info">
        <strong>安装前请确保：</strong>
        <ul style="margin-top: 10px; padding-left: 20px;">
            <li>已准备好MySQL数据库</li>
            <li>PHP版本 >= 7.4</li>
            <li>PDO MySQL扩展已启用</li>
            <li>Web服务器已正确配置</li>
        </ul>
    </div>
    
    <h3>系统功能特性</h3>
    <ul style="margin: 15px 0; padding-left: 20px;">
        <li>📝 工作周报在线编辑</li>
        <li>📅 日历视图管理</li>
        <li>👥 多用户账号管理</li>
        <li>📊 导出CSV/PDF功能</li>
        <li>📈 数据统计分析</li>
        <li>🔐 安全的用户认证</li>
        <li>💾 数据库持久化存储</li>
    </ul>
    
    <div class="btn-group">
        <button class="btn" onclick="nextStep()">开始安装</button>
    </div>
    <?php
}

function showDatabaseConfig() {
    ?>
    <h2>数据库配置</h2>
    <p>请填写数据库连接信息：</p>
    
    <form id="dbForm" method="post" action="install.php?step=3">
        <div class="form-group">
            <label for="db_host">数据库主机</label>
            <input type="text" id="db_host" name="db_host" value="localhost" required>
        </div>
        
        <div class="form-group">
            <label for="db_port">端口号</label>
            <input type="number" id="db_port" name="db_port" value="3306" required>
        </div>
        
        <div class="form-group">
            <label for="db_name">数据库名</label>
            <input type="text" id="db_name" name="db_name" value="work_report" required>
        </div>
        
        <div class="form-group">
            <label for="db_user">用户名</label>
            <input type="text" id="db_user" name="db_user" value="root" required>
        </div>
        
        <div class="form-group">
            <label for="db_pass">密码</label>
            <input type="password" id="db_pass" name="db_pass">
        </div>
        
        <div id="db-test-result"></div>
        
        <div class="btn-group">
            <button type="button" class="btn" onclick="testDatabase()">测试连接</button>
            <button type="submit" class="btn" id="installBtn" disabled>开始安装</button>
        </div>
    </form>
    <?php
}

function performInstallation() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $config = [
            'host' => $_POST['db_host'],
            'port' => $_POST['db_port'],
            'dbname' => $_POST['db_name'],
            'username' => $_POST['db_user'],
            'password' => $_POST['db_pass']
        ];
        
        echo '<h2>正在安装系统...</h2>';
        echo '<div class="progress"><div class="progress-bar" id="progressBar" style="width: 0%"></div></div>';
        echo '<div id="installLog"><pre id="logContent"></pre></div>';
        
        echo '<script>
        let progress = 0;
        let logContent = document.getElementById("logContent");
        let progressBar = document.getElementById("progressBar");
        
        function updateProgress(percent, message) {
            progress = percent;
            progressBar.style.width = percent + "%";
            logContent.textContent += "[" + new Date().toLocaleTimeString() + "] " + message + "\n";
            logContent.scrollTop = logContent.scrollHeight;
        }
        
        updateProgress(10, "开始安装...");
        </script>';
        
        flush();
        
        try {
            // 1. 测试数据库连接
            echo '<script>updateProgress(20, "测试数据库连接...");</script>';
            flush();
            
            $pdo = createDatabaseConnection($config);
            
            // 2. 创建数据库配置文件
            echo '<script>updateProgress(30, "创建配置文件...");</script>';
            flush();
            
            createConfigFile($config);
            
            // 3. 创建数据库表
            echo '<script>updateProgress(50, "创建数据库表...");</script>';
            flush();
            
            createTables($pdo);
            
            // 4. 创建默认管理员账号
            echo '<script>updateProgress(80, "创建默认管理员账号...");</script>';
            flush();
            
            createDefaultAdmin($pdo);
            
            // 5. 迁移现有数据
            echo '<script>updateProgress(90, "迁移现有数据...");</script>';
            flush();
            
            migrateOldData($pdo);
            
            // 6. 创建安装锁定文件
            echo '<script>updateProgress(100, "安装完成！");</script>';
            flush();
            
            file_put_contents('./install.lock', date('Y-m-d H:i:s'));
            
            echo '
            <div class="alert alert-success" style="margin-top: 20px;">
                <h3>🎉 安装完成！</h3>
                <p><strong>默认管理员账号：</strong></p>
                <ul>
                    <li>用户名: admin</li>
                    <li>密码: 123456</li>
                </ul>
                <p style="margin-top: 15px;">
                    <a href="index.php" class="btn">进入系统</a>
                </p>
            </div>';
            
        } catch (Exception $e) {
            echo '<script>updateProgress(' . $progress . ', "错误: ' . addslashes($e->getMessage()) . '");</script>';
            flush();
            
            echo '
            <div class="alert alert-error" style="margin-top: 20px;">
                <h3>安装失败</h3>
                <p><strong>错误信息：</strong></p>
                <pre style="background: #f5f5f5; padding: 10px; border-radius: 4px; white-space: pre-wrap;">' . htmlspecialchars($e->getMessage()) . '</pre>
                <p><strong>故障排除建议:</strong></p>
                <ul>
                    <li>检查数据库连接参数是否正确</li>
                    <li>确认数据库用户具有创建表的权限</li>
                    <li>检查目录写入权限</li>
                    <li>查看服务器错误日志获取更多信息</li>
                </ul>
            </div>
            
            <div class="btn-group">
                <button class="btn" onclick="history.back()">返回重试</button>
            </div>';
        }
    }
}

function createDatabaseConnection($config) {
    // 先尝试连接到数据库服务器（不指定数据库）
    $dsn = "mysql:host={$config['host']};port={$config['port']};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];
    
    $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
    
    // 检查数据库是否存在，不存在则创建
    $stmt = $pdo->prepare("CREATE DATABASE IF NOT EXISTS `{$config['dbname']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $stmt->execute();
    
    // 重新连接到指定的数据库
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
    
    return $pdo;
}

function createConfigFile($config) {
    $configContent = "<?php
// 工作周报表系统配置文件
define('APP_NAME', '森森信息部工作周报表');
define('VERSION', '2.0');

// 时区设置
date_default_timezone_set('Asia/Shanghai');

// 错误报告设置 - 在API调用时不显示错误到输出
error_reporting(E_ALL);
ini_set('display_errors', 0); // 改为0，避免错误信息干扰JSON输出
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// 数据库配置
\$config = [
    'host' => '{$config['host']}',
    'port' => {$config['port']},
    'dbname' => '{$config['dbname']}',
    'username' => '{$config['username']}',
    'password' => '{$config['password']}',
    'charset' => 'utf8mb4'
];

// 创建数据库连接
try {
    \$dsn = \"mysql:host={\$config['host']};port={\$config['port']};dbname={\$config['dbname']};charset={\$config['charset']}\";
    \$options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => \"SET NAMES {\$config['charset']} COLLATE {\$config['charset']}_unicode_ci\"
    ];
    \$pdo = new PDO(\$dsn, \$config['username'], \$config['password'], \$options);
} catch (PDOException \$e) {
    error_log(\"数据库连接失败: \" . \$e->getMessage());
    die(\"数据库连接失败，请检查配置\");
}

// Session配置
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>";
    
    if (!file_put_contents('./config.php', $configContent)) {
        throw new Exception('无法创建数据库配置文件');
    }
}

function createTables($pdo) {
    // 用户表
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100),
        role ENUM('admin', 'user') DEFAULT 'user',
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $pdo->exec($sql);
    
    // 工作周报表
    $sql = "CREATE TABLE IF NOT EXISTS reports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        report_date DATE NOT NULL,
        work_content TEXT,
        next_plan TEXT,
        issues TEXT,
        suggestions TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_date (user_id, report_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $pdo->exec($sql);
    
    // 操作日志表
    $sql = "CREATE TABLE IF NOT EXISTS logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        action VARCHAR(100) NOT NULL,
        description TEXT,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $pdo->exec($sql);
}

function createDefaultAdmin($pdo) {
    // 检查是否已存在管理员账号
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $stmt->execute();
    
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, name, role, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            'admin',
            password_hash('123456', PASSWORD_DEFAULT),
            '系统管理员',
            'admin',
            'active'
        ]);
    }
}

function migrateOldData($pdo) {
    $reportsFile = './reports.json';
    
    if (file_exists($reportsFile)) {
        $oldReports = json_decode(file_get_contents($reportsFile), true);
        
        if ($oldReports && is_array($oldReports)) {
            // 获取默认管理员ID
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin' LIMIT 1");
            $stmt->execute();
            $adminId = $stmt->fetchColumn();
            
            if ($adminId) {
                foreach ($oldReports as $date => $report) {
                    try {
                        $stmt = $pdo->prepare("INSERT IGNORE INTO reports (user_id, report_date, work_content, next_plan, issues, suggestions, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $adminId,
                            $date,
                            $report['work_content'] ?? '',
                            $report['next_plan'] ?? '',
                            $report['issues'] ?? '',
                            $report['suggestions'] ?? '',
                            $report['created_at'] ?? date('Y-m-d H:i:s')
                        ]);
                    } catch (PDOException $e) {
                        // 忽略重复数据错误
                        continue;
                    }
                }
            }
        }
        
        // 备份旧文件
        rename($reportsFile, $reportsFile . '.backup');
    }
}
?>
