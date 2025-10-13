<?php
require_once 'config.php';
require_once 'auth.php';

// 检查用户是否已登录
$auth = new Auth($pdo);
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user = $auth->getCurrentUser();

// 处理PDF导出请求
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'export_pdf') {
    try {
        // 从数据库读取报告数据
        $reports = [];
        
        if ($user['role'] === 'admin') {
            // 管理员可以看到所有用户的报告
            $stmt = $pdo->prepare("
                SELECT r.*, u.name as user_name 
                FROM reports r 
                LEFT JOIN users u ON r.user_id = u.id 
                ORDER BY r.report_date DESC
            ");
            $stmt->execute();
        } else {
            // 普通用户只能看到自己的报告
            $stmt = $pdo->prepare("
                SELECT r.*, u.name as user_name 
                FROM reports r 
                LEFT JOIN users u ON r.user_id = u.id 
                WHERE r.user_id = ? 
                ORDER BY r.report_date DESC
            ");
            $stmt->execute([$user['id']]);
        }
        
        $result = $stmt->fetchAll();
        
        // 转换数据格式以兼容现有的导出代码
        foreach ($result as $row) {
            $reports[$row['report_date']] = [
                'reporter_name' => $row['user_name'],
                'work_content' => $row['work_content'],
                'next_plan' => $row['next_plan'],
                'issues' => $row['issues'],
                'suggestions' => $row['suggestions'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at']
            ];
        }
        
        if (empty($reports)) {
            http_response_code(400);
            echo json_encode(['error' => '没有数据可以导出'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 按日期倒序排序（最新的在前面）
        krsort($reports);
        
        // 检查是否指定了特定的报告
        $target_date = $_GET['date'] ?? '';
        $target_name = $_GET['name'] ?? '';
        
        // 如果指定了日期和姓名，只导出该报告
        if (!empty($target_date) && !empty($target_name)) {
            if (isset($reports[$target_date]) && $reports[$target_date]['reporter_name'] === $target_name) {
                $single_report = [$target_date => $reports[$target_date]];
                exportSingleReport($single_report, $target_date, $target_name);
            } else {
                http_response_code(404);
                echo json_encode(['error' => '未找到指定的报告'], JSON_UNESCAPED_UNICODE);
            }
            exit;
        }
        
        // 默认导出所有报告
        exportAllReports($reports);
        exit;
        
    } catch (Exception $e) {
        error_log("PDF导出错误: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => '导出失败: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// 导出单个报告的函数
function exportSingleReport($reports, $date, $name) {
    $report = $reports[$date];
    
    // 计算周信息
    $dateObj = new DateTime($date);
    $weekNumber = $dateObj->format('W');
    $year = $dateObj->format('Y');
    
    // 生成文件名：姓名+年份+第几周+森森工作周报表
    $filename = $name . $year . '年第' . $weekNumber . '周森森工作周报表';
    
    // 设置响应头，建议浏览器下载文件
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: inline; filename="' . $filename . '.html"');
    
    generateReportHTML([$date => $report], $filename, true);
}

// 导出所有报告的函数
function exportAllReports($reports) {
    $filename = '森森信息部工作周报表汇总';
    
    // 设置响应头为HTML，让浏览器显示可打印的页面
    header('Content-Type: text/html; charset=utf-8');
    
    // 获取分页参数
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $per_page = 5; // 每页显示5条
    
    generateReportHTML($reports, $filename, false, $page, $per_page);
}

// 生成报告HTML的函数
function generateReportHTML($reports, $title, $is_single = false, $page = 1, $per_page = 5) {
    // 计算分页
    $total_reports = count($reports);
    $total_pages = ceil($total_reports / $per_page);
    $page = max(1, min($page, $total_pages)); // 确保页码在有效范围内
    
    // 获取当前页的报告
    $reports_array = $reports; // 保留所有报告用于计算总数
    if (!$is_single && $total_reports > $per_page) {
        $offset = ($page - 1) * $per_page;
        $reports = array_slice($reports, $offset, $per_page, true);
    }
    
    echo '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($title) . '</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
            .page-break { page-break-before: always; }
        }
        
        body {
            font-family: "PingFang SC", "Hiragino Sans GB", "Microsoft YaHei", "SimSun", sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: white;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #667eea;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #667eea;
            font-size: 28px;
            margin: 0 0 10px 0;
        }
        
        .header .meta {
            color: #666;
            font-size: 14px;
        }
        
        .report-item {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 25px;
            padding: 20px;
            background: #fafafa;
        }
        
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .report-date {
            background: #667eea;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
        }
        
        .report-name {
            background: #28a745;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
        }
        
        .report-section {
            margin-bottom: 15px;
        }
        
        .report-section h3 {
            color: #495057;
            font-size: 16px;
            margin: 0 0 8px 0;
            padding: 5px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .report-section .content {
            background: white;
            padding: 12px;
            border-radius: 4px;
            border-left: 4px solid #667eea;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .print-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .btn {
            padding: 8px 16px;
            margin: 0 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .week-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 30px 0;
            gap: 10px;
        }
        
        .pagination a, .pagination span {
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            text-decoration: none;
            color: #667eea;
            background: white;
        }
        
        .pagination .current {
            background: #667eea;
            color: white;
            border-color: #667eea;
            font-weight: bold;
        }
        
        .pagination a:hover {
            background: #f0f0f0;
        }
        
        .pagination .disabled {
            color: #ccc;
            cursor: not-allowed;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="print-controls no-print">
        <button class="btn btn-primary" onclick="window.print()">🖨️ 打印/保存PDF</button>
        <a href="index.php" class="btn btn-secondary">← 返回</a>';
        
    if (!$is_single) {
        echo '<div style="margin-top: 10px;">
            <small>💡 提示：点击下方的单独导出链接可以生成单个报告的PDF文件</small>
        </div>';
    }
    
    echo '</div>
    
    <div class="header">
        <h1>📋 ' . htmlspecialchars($title) . '</h1>
        <div class="meta">
            <p>生成时间：' . date('Y年m月d日 H:i:s') . '</p>
            <p>总计报告：' . $total_reports . ' 份</p>';
            
    if (!$is_single && $total_pages > 1) {
        echo '<p>当前页：第 ' . $page . ' 页 / 共 ' . $total_pages . ' 页</p>';
    }
    
    echo '</div>
    </div>';
    
    $count = 0;
    foreach ($reports as $date => $report) {
        $count++;
        
        // 计算周信息
        $dateObj = new DateTime($date);
        $weekNumber = $dateObj->format('W');
        $year = $dateObj->format('Y');
        
        // 计算周的开始和结束日期
        $weekStart = clone $dateObj;
        $weekStart->setISODate($year, $weekNumber, 1);
        $weekEnd = clone $dateObj;
        $weekEnd->setISODate($year, $weekNumber, 7);
        
        $weekInfo = "第{$weekNumber}周 " . $weekStart->format('Y/m/d') . "-" . $weekEnd->format('Y/m/d');
        
        echo '<div class="report-item">
            <div class="report-header">
                <div class="report-date">📅 ' . $date . '</div>';
                
        if (!empty($report['reporter_name'])) {
            echo '<div class="report-name">👤 ' . htmlspecialchars($report['reporter_name']) . '</div>';
        }
        
        echo '</div>
            <div class="week-info">' . $weekInfo . '</div>';
            
        // 如果不是单个报告，显示单独导出链接
        if (!$is_single && !empty($report['reporter_name'])) {
            $reporter_name = urlencode($report['reporter_name']);
            $export_date = urlencode($date);
            echo '<div class="no-print" style="margin: 10px 0;">
                <a href="pdf_export.php?action=export_pdf&date=' . $export_date . '&name=' . $reporter_name . '" 
                   class="btn btn-secondary" target="_blank" style="font-size: 12px; padding: 4px 8px;">
                   📄 单独导出此报告
                </a>
            </div>';
        }
            
        if (!empty($report['work_content'])) {
            echo '<div class="report-section">
                <h3>📝 本周工作内容</h3>
                <div class="content">' . htmlspecialchars($report['work_content']) . '</div>
            </div>';
        }
        
        if (!empty($report['next_plan'])) {
            echo '<div class="report-section">
                <h3>📋 下周工作计划</h3>
                <div class="content">' . htmlspecialchars($report['next_plan']) . '</div>
            </div>';
        }
        
        if (!empty($report['issues'])) {
            echo '<div class="report-section">
                <h3>⚠️ 遇到的问题</h3>
                <div class="content">' . htmlspecialchars($report['issues']) . '</div>
            </div>';
        }
        
        if (!empty($report['suggestions'])) {
            echo '<div class="report-section">
                <h3>💡 建议与改进</h3>
                <div class="content">' . htmlspecialchars($report['suggestions']) . '</div>
            </div>';
        }
        
        echo '</div>';
        
        // 打印模式下每3个报告后分页
        if ($count % 3 === 0 && $count < count($reports)) {
            echo '<div class="page-break"></div>';
        }
    }
    
    // 添加分页导航（只在非单个报告且有多页时显示）
    if (!$is_single && $total_pages > 1) {
        echo '<div class="pagination no-print">';
        
        // 上一页
        if ($page > 1) {
            echo '<a href="pdf_export.php?action=export_pdf&page=' . ($page - 1) . '">← 上一页</a>';
        } else {
            echo '<span class="disabled">← 上一页</span>';
        }
        
        // 页码
        $start_page = max(1, $page - 2);
        $end_page = min($total_pages, $page + 2);
        
        if ($start_page > 1) {
            echo '<a href="pdf_export.php?action=export_pdf&page=1">1</a>';
            if ($start_page > 2) {
                echo '<span>...</span>';
            }
        }
        
        for ($i = $start_page; $i <= $end_page; $i++) {
            if ($i == $page) {
                echo '<span class="current">' . $i . '</span>';
            } else {
                echo '<a href="pdf_export.php?action=export_pdf&page=' . $i . '">' . $i . '</a>';
            }
        }
        
        if ($end_page < $total_pages) {
            if ($end_page < $total_pages - 1) {
                echo '<span>...</span>';
            }
            echo '<a href="pdf_export.php?action=export_pdf&page=' . $total_pages . '">' . $total_pages . '</a>';
        }
        
        // 下一页
        if ($page < $total_pages) {
            echo '<a href="pdf_export.php?action=export_pdf&page=' . ($page + 1) . '">下一页 →</a>';
        } else {
            echo '<span class="disabled">下一页 →</span>';
        }
        
        echo '</div>';
    }
    
    echo '<script>
        // 如果URL包含auto_print参数，自动打开打印对话框
        if (window.location.search.includes("auto_print=1")) {
            setTimeout(function() {
                window.print();
            }, 1000);
        }
    </script>
    
</body>
</html>';
}
?>
