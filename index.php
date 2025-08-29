

<?php
require_once 'config.php';
require_once 'auth.php';

$auth = new Auth($pdo);

// 检查登录状态，如果已登录则跳转到主页面，否则跳转到登录页面
if ($auth->isLoggedIn()) {
    header('Location: main.php');
} else {
    header('Location: login.php');
}
exit();
?>
