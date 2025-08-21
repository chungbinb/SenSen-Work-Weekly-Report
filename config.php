<?php
// 简单的配置文件
define('APP_NAME', '工作周报表系统');
define('VERSION', '1.0');

// 时区设置
date_default_timezone_set('Asia/Shanghai');

// 错误报告设置
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
?>
