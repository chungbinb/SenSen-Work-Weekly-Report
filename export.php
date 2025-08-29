<?php
// 简化的CSV导出，不依赖外部库
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="森森信息部工作周报表_' . date('Y年m月d日') . '.csv"');
header('Cache-Control: max-age=0');

// 输出BOM以支持中文
echo "\xEF\xBB\xBF";

// 读取报告数据
$reports = [];
if (file_exists('reports.json')) {
    $reports = json_decode(file_get_contents('reports.json'), true) ?: [];
}

// 输出CSV头部
echo "日期,姓名,本周工作内容,下周工作计划,遇到的问题,建议与改进,创建时间,更新时间\n";

// 按日期排序
ksort($reports);

// 输出数据
foreach ($reports as $date => $report) {
    $row = [
        $date,
        '"' . str_replace('"', '""', $report['reporter_name'] ?? '') . '"',
        '"' . str_replace('"', '""', $report['work_content'] ?? '') . '"',
        '"' . str_replace('"', '""', $report['next_plan'] ?? '') . '"',
        '"' . str_replace('"', '""', $report['issues'] ?? '') . '"',
        '"' . str_replace('"', '""', $report['suggestions'] ?? '') . '"',
        $report['created_at'] ?? '',
        $report['updated_at'] ?? ''
    ];
    
    echo implode(',', $row) . "\n";
}

// 如果没有数据
if (empty($reports)) {
    echo "暂无数据\n";
}
?>
