<?php
// 快速诊断和修复脚本
echo "<h1>森森信息部工作周报表系统 - 快速修复</h1>";

// 1. 检查并创建reports.json
if (!file_exists('reports.json')) {
    file_put_contents('reports.json', '{}');
    echo "<p>✅ 创建了reports.json文件</p>";
}

// 2. 设置文件权限
if (chmod('reports.json', 0666)) {
    echo "<p>✅ 设置reports.json权限成功</p>";
} else {
    echo "<p>❌ 设置reports.json权限失败</p>";
}

// 3. 测试写入权限
$test_data = ['test' => 'data'];
if (file_put_contents('reports.json', json_encode($test_data))) {
    echo "<p>✅ 文件写入测试成功</p>";
    // 恢复空文件
    file_put_contents('reports.json', '{}');
} else {
    echo "<p>❌ 文件写入测试失败</p>";
}

// 4. 显示当前状态
echo "<h2>当前状态</h2>";
echo "<p>reports.json存在: " . (file_exists('reports.json') ? '是' : '否') . "</p>";
echo "<p>reports.json可读: " . (is_readable('reports.json') ? '是' : '否') . "</p>";
echo "<p>reports.json可写: " . (is_writable('reports.json') ? '是' : '否') . "</p>";
echo "<p>目录可写: " . (is_writable('.') ? '是' : '否') . "</p>";

echo "<h2>手动测试</h2>";
echo "<form method='post'>";
echo "<p>测试数据: <input type='text' name='test_content' value='测试内容'></p>";
echo "<p><input type='submit' name='test_save' value='测试保存'></p>";
echo "</form>";

if (isset($_POST['test_save'])) {
    $test_content = $_POST['test_content'];
    $test_date = date('Y-m-d');
    
    $data = [];
    if (file_exists('reports.json')) {
        $data = json_decode(file_get_contents('reports.json'), true) ?: [];
    }
    
    $data[$test_date] = [
        'date' => $test_date,
        'reporter_name' => '测试用户',
        'work_content' => $test_content,
        'next_plan' => '',
        'issues' => '',
        'suggestions' => '',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    if (file_put_contents('reports.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        echo "<p style='color: green;'>✅ 测试保存成功！</p>";
    } else {
        echo "<p style='color: red;'>❌ 测试保存失败！</p>";
    }
}

echo "<hr>";
echo "<p><a href='index.php'>返回主页</a> | <a href='debug.php'>详细调试</a> | <a href='test_api.html'>API测试</a></p>";
?>
