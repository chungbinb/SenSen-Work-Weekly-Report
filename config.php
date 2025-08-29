<?php
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
$config = [
    'host' => 'localhost',
    'port' => 3306,
    'dbname' => 'job',
    'username' => 'job',
    'password' => 'wyYXZsdiZjjbPYyd',
    'charset' => 'utf8mb4'
];

// 创建数据库连接
try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$config['charset']} COLLATE {$config['charset']}_unicode_ci"
    ];
    $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
} catch (PDOException $e) {
    error_log("数据库连接失败: " . $e->getMessage());
    // 不使用die()，而是抛出异常让调用方处理
    throw new Exception("数据库连接失败: " . $e->getMessage());
}

// Session配置
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>