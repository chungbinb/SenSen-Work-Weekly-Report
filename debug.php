<?php
require_once 'config.php';

echo "<h1>森森信息部工作周报表系统调试页面</h1>";

echo "<h2>1. 文件权限检查</h2>";
$files = ['reports.json', 'api.php', 'config.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        $perms = fileperms($file);
        $readable = is_readable($file) ? '✅ 可读' : '❌ 不可读';
        $writable = is_writable($file) ? '✅ 可写' : '❌ 不可写';
        echo "<p><strong>$file</strong>: $readable, $writable (权限: " . substr(sprintf('%o', $perms), -4) . ")</p>";
    } else {
        echo "<p><strong>$file</strong>: ❌ 文件不存在</p>";
    }
}

echo "<h2>2. 目录权限检查</h2>";
$dir = __DIR__;
$readable = is_readable($dir) ? '✅ 可读' : '❌ 不可读';
$writable = is_writable($dir) ? '✅ 可写' : '❌ 不可写';
echo "<p><strong>当前目录</strong>: $readable, $writable</p>";

echo "<h2>3. Reports.json 文件内容</h2>";
if (file_exists('reports.json')) {
    $content = file_get_contents('reports.json');
    echo "<pre>" . htmlspecialchars($content) . "</pre>";
    
    $reports = json_decode($content, true);
    if ($reports === null) {
        echo "<p>❌ JSON解析失败: " . json_last_error_msg() . "</p>";
    } else {
        echo "<p>✅ JSON解析成功，包含 " . count($reports) . " 条记录</p>";
    }
} else {
    echo "<p>❌ reports.json 文件不存在</p>";
}

echo "<h2>4. 测试API接口</h2>";
echo "<p><a href='api.php?action=get_all_reports' target='_blank'>测试获取所有报告</a></p>";

echo "<h2>5. 手动创建测试数据</h2>";
if (isset($_GET['create_test'])) {
    $test_data = [
        date('Y-m-d') => [
            'date' => date('Y-m-d'),
            'reporter_name' => '测试用户',
            'work_content' => '这是测试的工作内容',
            'next_plan' => '这是测试的下周计划',
            'issues' => '这是测试的问题',
            'suggestions' => '这是测试的建议',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]
    ];
    
    if (file_put_contents('reports.json', json_encode($test_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        echo "<p>✅ 测试数据创建成功！</p>";
    } else {
        echo "<p>❌ 测试数据创建失败！</p>";
    }
} else {
    echo "<p><a href='?create_test=1'>点击创建测试数据</a></p>";
}

echo "<h2>6. 清空数据</h2>";
if (isset($_GET['clear_data'])) {
    if (file_exists('reports.json') && unlink('reports.json')) {
        echo "<p>✅ 数据清空成功！</p>";
    } else {
        echo "<p>❌ 数据清空失败！</p>";
    }
} else {
    echo "<p><a href='?clear_data=1' onclick='return confirm(\"确定要清空所有数据吗？\")'>点击清空所有数据</a></p>";
}

echo "<h2>7. PHP环境信息</h2>";
echo "<p>PHP版本: " . PHP_VERSION . "</p>";
echo "<p>时区: " . date_default_timezone_get() . "</p>";
echo "<p>当前时间: " . date('Y-m-d H:i:s') . "</p>";

echo "<hr>";
echo "<p><a href='index.php'>返回主页</a></p>";
?>
