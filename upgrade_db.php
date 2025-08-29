<?php
/**
 * 数据库升级脚本 - 为现有安装添加last_login字段
 * 版本: 2.0.1
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== 数据库升级脚本 ===\n";
echo "版本: 2.0.1\n";
echo "升级内容: 为users表添加last_login字段\n\n";

try {
    // 检查config.php文件是否存在
    if (!file_exists('./config.php')) {
        throw new Exception('配置文件不存在，请先完成系统安装');
    }
    
    require_once './config.php';
    
    echo "✓ 配置文件加载成功\n";
    
    // 检查数据库连接
    if (!isset($pdo)) {
        throw new Exception('数据库连接未定义');
    }
    
    echo "✓ 数据库连接正常\n";
    
    // 检查users表是否存在
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() == 0) {
        throw new Exception('users表不存在，请检查数据库安装');
    }
    
    echo "✓ users表存在\n";
    
    // 检查last_login字段是否已存在
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'last_login'");
    if ($stmt->rowCount() > 0) {
        echo "✓ last_login字段已存在，无需升级\n";
    } else {
        echo "正在添加last_login字段...\n";
        
        // 添加last_login字段
        $sql = "ALTER TABLE users ADD COLUMN last_login TIMESTAMP NULL DEFAULT NULL AFTER updated_at";
        $pdo->exec($sql);
        
        echo "✓ last_login字段添加成功\n";
        
        // 验证字段是否添加成功
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'last_login'");
        if ($stmt->rowCount() > 0) {
            echo "✓ 字段验证成功\n";
        } else {
            throw new Exception('字段添加失败');
        }
    }
    
    // 显示当前表结构
    echo "\n--- 当前users表结构 ---\n";
    $stmt = $pdo->query("DESCRIBE users");
    while ($row = $stmt->fetch()) {
        echo sprintf("%-15s %-20s %-8s %-8s\n", 
            $row['Field'], 
            $row['Type'], 
            $row['Null'], 
            $row['Key']
        );
    }
    
    // 检查现有用户数量
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $count = $stmt->fetch();
    echo "\n✓ 现有用户数量: {$count['count']}\n";
    
    echo "\n🎉 数据库升级完成！\n";
    echo "\n注意事项:\n";
    echo "- last_login字段已添加到users表\n";
    echo "- 现有用户的last_login值为NULL（从未登录）\n";
    echo "- 用户下次登录时会自动更新last_login时间\n";
    echo "- 用户管理页面现在可以正常显示最后登录时间\n";
    
} catch (Exception $e) {
    echo "\n✗ 升级失败: " . $e->getMessage() . "\n";
    echo "\n故障排除:\n";
    echo "1. 检查数据库连接是否正常\n";
    echo "2. 确认数据库用户具有ALTER TABLE权限\n";
    echo "3. 检查config.php文件是否正确\n";
    echo "4. 查看错误日志获取更多信息\n";
    
    if (isset($e) && method_exists($e, 'getFile')) {
        echo "\n错误详情:\n";
        echo "文件: " . $e->getFile() . "\n";
        echo "行号: " . $e->getLine() . "\n";
    }
}

echo "\n=== 升级脚本结束 ===\n";
?>
