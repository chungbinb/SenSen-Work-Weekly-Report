<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_all_reports':
        getAllReports();
        break;
    
    case 'get_report':
        getReport();
        break;
    
    case 'save_report':
        saveReport();
        break;
    
    case 'delete_report':
        deleteReport();
        break;
    
    default:
        echo json_encode([
            'success' => false,
            'message' => '无效的操作'
        ], JSON_UNESCAPED_UNICODE);
        break;
}

function getAllReports() {
    $reports_file = 'reports.json';
    
    if (file_exists($reports_file)) {
        $reports = json_decode(file_get_contents($reports_file), true);
        echo json_encode([
            'success' => true,
            'reports' => $reports ?: []
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => true,
            'reports' => []
        ], JSON_UNESCAPED_UNICODE);
    }
}

function getReport() {
    $date = $_GET['date'] ?? '';
    
    if (empty($date)) {
        echo json_encode([
            'success' => false,
            'message' => '日期参数不能为空'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    $reports_file = 'reports.json';
    
    if (file_exists($reports_file)) {
        $reports = json_decode(file_get_contents($reports_file), true);
        
        if (isset($reports[$date])) {
            echo json_encode([
                'success' => true,
                'report' => $reports[$date]
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false,
                'message' => '未找到该日期的报告'
            ], JSON_UNESCAPED_UNICODE);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => '报告文件不存在'
        ], JSON_UNESCAPED_UNICODE);
    }
}

function saveReport() {
    // 记录调试信息
    error_log("saveReport called at " . date('Y-m-d H:i:s'));
    error_log("POST data: " . print_r($_POST, true));
    
    $selected_date = $_POST['selected_date'] ?? '';
    $reporter_name = $_POST['reporter_name'] ?? '';
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
    
    if (empty($reporter_name)) {
        error_log("Error: 姓名不能为空");
        echo json_encode([
            'success' => false,
            'message' => '姓名不能为空'
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
    
    $report_data = [
        'date' => $selected_date,
        'reporter_name' => $reporter_name,
        'work_content' => $work_content,
        'next_plan' => $next_plan,
        'issues' => $issues,
        'suggestions' => $suggestions,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $reports_file = 'reports.json';
    $reports = [];
    
    if (file_exists($reports_file)) {
        $reports = json_decode(file_get_contents($reports_file), true) ?: [];
    }
    
    // 如果已存在，则更新更新时间
    if (isset($reports[$selected_date])) {
        $report_data['created_at'] = $reports[$selected_date]['created_at'] ?? date('Y-m-d H:i:s');
    }
    
    $reports[$selected_date] = $report_data;
    
    error_log("Trying to save to file: $reports_file");
    error_log("Data to save: " . json_encode($reports, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    $result = file_put_contents($reports_file, json_encode($reports, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    if ($result !== false) {
        error_log("Save successful, bytes written: $result");
        echo json_encode([
            'success' => true,
            'message' => '周报保存成功',
            'report' => $report_data
        ], JSON_UNESCAPED_UNICODE);
    } else {
        error_log("Save failed! File permissions issue?");
        echo json_encode([
            'success' => false,
            'message' => '保存失败，请检查文件权限'
        ], JSON_UNESCAPED_UNICODE);
    }
}

function deleteReport() {
    $date = $_POST['date'] ?? '';
    
    if (empty($date)) {
        echo json_encode([
            'success' => false,
            'message' => '日期参数不能为空'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    $reports_file = 'reports.json';
    
    if (file_exists($reports_file)) {
        $reports = json_decode(file_get_contents($reports_file), true);
        
        if (isset($reports[$date])) {
            unset($reports[$date]);
            
            if (file_put_contents($reports_file, json_encode($reports, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
                echo json_encode([
                    'success' => true,
                    'message' => '报告删除成功'
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => '删除失败，请检查文件权限'
                ], JSON_UNESCAPED_UNICODE);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => '未找到该日期的报告'
            ], JSON_UNESCAPED_UNICODE);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => '报告文件不存在'
        ], JSON_UNESCAPED_UNICODE);
    }
}
?>
