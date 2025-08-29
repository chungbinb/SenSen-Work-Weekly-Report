<?php
require_once 'config.php';
require_once 'auth.php';

$auth = new Auth($pdo);

// 检查登录状态
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>森森信息部工作周报表</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'PingFang SC', 'Hiragino Sans GB', 'Microsoft YaHei', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 30px;
            overflow: visible; /* 改为visible，允许下拉菜单显示 */
            position: relative; /* 确保定位上下文 */
        }

        .header-content {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 15px; /* 保持圆角 */
            position: relative; /* 确保定位上下文 */
        }

        .header-left h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .header-left p {
            opacity: 0.9;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .user-role {
            opacity: 0.8;
            font-size: 0.9rem;
        }

        .user-menu {
            position: relative;
            z-index: 1000; /* 确保父容器有足够的z-index */
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .user-avatar:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.1);
        }

        .dropdown-menu {
            position: absolute;
            top: 60px;
            right: 0;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            min-width: 200px;
            display: none;
            z-index: 9999; /* 提高z-index值 */
            border: 1px solid #e9ecef; /* 添加边框确保可见性 */
        }

        .dropdown-menu.show {
            display: block;
            animation: slideDown 0.3s ease;
            /* 确保在任何情况下都可见 */
            visibility: visible;
            opacity: 1;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-item {
            padding: 12px 20px;
            color: #333;
            text-decoration: none;
            display: block;
            transition: background 0.3s ease;
            border-bottom: 1px solid #f1f3f4;
        }

        .dropdown-item:last-child {
            border-bottom: none;
        }

        .dropdown-item:hover {
            background: #f8f9fa;
        }

        .dropdown-item:first-child {
            border-radius: 10px 10px 0 0;
        }

        .dropdown-item:last-child {
            border-radius: 0 0 10px 10px;
        }

        .main-content {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
        }

        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .card-header h2 {
            font-size: 1.5rem;
            margin: 0;
        }

        .card-body {
            padding: 20px;
        }

        /* 日历样式 */
        .calendar-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .nav-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .nav-btn:hover {
            background: #5a6fd8;
        }

        .calendar-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .calendar-table th,
        .calendar-table td {
            text-align: center;
            padding: 8px 4px;
            border: 1px solid #e9ecef;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            vertical-align: top;
            min-height: 45px;
            max-height: 65px;
            font-size: 14px;
            line-height: 1.2;
        }

        .calendar-table td .date-number {
            font-weight: bold;
            margin-bottom: 2px;
        }

        .calendar-table td .lunar-date {
            font-size: 10px;
            color: #6c757d;
            line-height: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .calendar-table td.today .lunar-date {
            color: #856404;
        }

        .calendar-table td.has-report .lunar-date {
            color: #0f5132;
        }

        /* 周信息样式优化 */
        #week-info {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            padding: 8px 12px;
            border-radius: 6px;
            border-left: 4px solid #2196f3;
            font-size: 13px;
            line-height: 1.4;
        }

        /* 节日标识 */
        .festival-indicator {
            display: inline-block;
            background: #ff4757;
            color: white;
            font-size: 8px;
            padding: 1px 3px;
            border-radius: 2px;
            position: absolute;
            top: 1px;
            left: 1px;
        }

        .calendar-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }

        .calendar-table td:hover {
            background: #e3f2fd;
            transform: scale(1.05);
        }

        .calendar-table td.has-report {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            font-weight: bold;
            color: #155724;
            border: 2px solid #28a745;
            position: relative;
        }

        .calendar-table td.has-report::after {
            content: '📝';
            position: absolute;
            top: -2px;
            right: -2px;
            background: #28a745;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            animation: pulse-icon 2s infinite;
        }

        @keyframes pulse-icon {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .calendar-table td.selected {
            background: #007bff;
            color: white;
        }

        .week-number {
            background: #6c757d !important;
            color: white !important;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .week-number.current-week {
            background: #28a745 !important;
            color: white !important;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }

        .calendar-table td.today {
            background: #fff3cd !important;
            border: 2px solid #ffc107;
            font-weight: bold;
        }

        /* 表单样式 */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
        }

        .form-input,
        .form-textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-input:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
        }

        /* 消息提示 */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid transparent;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .alert-error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        /* 响应式设计 */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .header-right {
                flex-direction: column;
                gap: 10px;
            }
            
            .main-content {
                gap: 20px;
            }
            
            .calendar-table th,
            .calendar-table td {
                padding: 8px 4px;
                font-size: 12px;
            }
        }

        /* 加载动画 */
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    <script src="lunar.js"></script>
    <script src="thai.js"></script>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <div class="header-left">
                    <h1>📅 森森信息部工作周报表</h1>
                    <p>高效管理您的工作周报，支持在线编辑和导出功能</p>
                </div>
                <div class="header-right">
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($user['name']); ?></div>
                        <div class="user-role"><?php echo $user['role'] === 'admin' ? '管理员' : '用户'; ?></div>
                    </div>
                    <div class="user-menu">
                        <div class="user-avatar" onclick="toggleUserMenu()">
                            👤
                        </div>
                        <div class="dropdown-menu" id="userDropdown">
                            <a href="#" class="dropdown-item" onclick="showPasswordModal()">修改密码</a>
                            <?php if ($user['role'] === 'admin'): ?>
                            <a href="user_management.php" class="dropdown-item">用户管理</a>
                            <?php endif; ?>
                            <a href="#" class="dropdown-item" onclick="logout()">退出登录</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="alert-container"></div>

        <div class="main-content">
            <!-- 日历部分 -->
            <div class="card">
                <div class="card-header">
                    <h2>📆 日历视图</h2>
                </div>
                <div class="card-body">
                    <div id="report-stats" style="background: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; font-size: 14px;">
                        <span id="stats-text">加载中...</span>
                    </div>
                    
                    <div class="calendar-nav">
                        <button class="nav-btn" onclick="changeMonth(-1)">← 上月</button>
                        <h3 id="current-month"></h3>
                        <button class="nav-btn" onclick="changeMonth(1)">下月 →</button>
                    </div>
                    <div style="text-align: center; margin-bottom: 15px; display: flex; justify-content: center; align-items: center; gap: 15px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <label for="calendar-type" style="font-weight: 600; color: #495057;">日历类型:</label>
                            <select id="calendar-type" onchange="changeCalendarType()" style="padding: 6px 12px; border: 1px solid #ddd; border-radius: 5px; background: white; font-size: 14px;">
                                <option value="chinese">🏮 中国农历</option>
                                <option value="thai">🇹🇭 泰国佛历</option>
                            </select>
                        </div>
                        <button class="nav-btn" onclick="goToToday()">📅 回到今天</button>
                    </div>
                    
                    <table class="calendar-table" id="calendar-table">
                        <thead>
                            <tr>
                                <th class="week-number">周</th>
                                <th>一</th>
                                <th>二</th>
                                <th>三</th>
                                <th>四</th>
                                <th>五</th>
                                <th>六</th>
                                <th>日</th>
                            </tr>
                        </thead>
                        <tbody id="calendar-body">
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 报表表单 -->
            <div class="card">
                <div class="card-header">
                    <h2>📝 工作周报表</h2>
                </div>
                <div class="card-body">
                    <form id="reportForm">
                        <div class="form-group">
                            <label for="selected_date" class="form-label">选择日期</label>
                            <input type="date" class="form-input" id="selected_date" name="selected_date" required>
                            <div id="week-info" style="margin-top: 8px; font-size: 14px; color: #6c757d;"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="work_content" class="form-label">本周工作内容</label>
                            <textarea class="form-textarea" id="work_content" name="work_content" placeholder="请输入本周完成的工作内容..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="next_plan" class="form-label">下周工作计划</label>
                            <textarea class="form-textarea" id="next_plan" name="next_plan" placeholder="请输入下周的工作计划..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="issues" class="form-label">遇到的问题</label>
                            <textarea class="form-textarea" id="issues" name="issues" placeholder="请输入工作中遇到的问题..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="suggestions" class="form-label">建议与改进</label>
                            <textarea class="form-textarea" id="suggestions" name="suggestions" placeholder="请输入建议与改进意见..."></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">💾 保存周报</button>
                            <button type="button" class="btn btn-success" onclick="exportToExcel()">📊 导出CSV</button>
                            <button type="button" class="btn btn-success" onclick="exportToPDF()">📄 导出PDF汇总</button>
                        </div>
                        
                        <div style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 5px; font-size: 14px; color: #666;">
                            💡 <strong>导出说明：</strong><br>
                            • 📄 导出PDF汇总：导出所有报告的汇总文件<br>
                            • 📋 单独导出：在PDF汇总页面中，每个报告都有"单独导出"按钮，文件名格式为：姓名+年份+第几周+森森工作周报表<br>
                            • 🧪 <a href="test_pdf_export.html" target="_blank" style="color: #667eea;">测试PDF导出功能</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 修改密码模态框 -->
    <div id="passwordModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 15px; width: 400px; max-width: 90%;">
            <h3 style="margin-bottom: 20px;">修改密码</h3>
            <form id="passwordForm">
                <div class="form-group">
                    <label for="old_password" class="form-label">原密码</label>
                    <input type="password" class="form-input" id="old_password" name="old_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password" class="form-label">新密码</label>
                    <input type="password" class="form-input" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password" class="form-label">确认新密码</label>
                    <input type="password" class="form-input" id="confirm_password" name="confirm_password" required>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn" onclick="hidePasswordModal()" style="background: #6c757d; color: white;">取消</button>
                    <button type="submit" class="btn btn-primary">确认修改</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentYear = new Date().getFullYear();
        let currentMonth = new Date().getMonth() + 1;
        let reports = {};
        let selectedDate = '';
        let currentUser = <?php echo json_encode($user); ?>;
        let calendarType = 'chinese'; // 默认显示中国农历

        // 初始化
        document.addEventListener('DOMContentLoaded', function() {
            loadReports();
            renderCalendar();
            setDefaultDate();
        });

        // 切换用户菜单
        function toggleUserMenu() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }

        // 点击其他地方关闭菜单
        document.addEventListener('click', function(e) {
            const userMenu = document.querySelector('.user-menu');
            const dropdown = document.getElementById('userDropdown');
            
            if (!userMenu.contains(e.target)) {
                dropdown.classList.remove('show');
            }
        });

        // 显示修改密码模态框
        function showPasswordModal() {
            document.getElementById('passwordModal').style.display = 'block';
            document.getElementById('userDropdown').classList.remove('show');
        }

        // 隐藏修改密码模态框
        function hidePasswordModal() {
            document.getElementById('passwordModal').style.display = 'none';
            document.getElementById('passwordForm').reset();
        }

        // 修改密码表单提交
        document.getElementById('passwordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const oldPassword = document.getElementById('old_password').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                showAlert('两次输入的新密码不一致', 'error');
                return;
            }
            
            if (newPassword.length < 6) {
                showAlert('新密码长度不能少于6位', 'error');
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'change_password');
                formData.append('old_password', oldPassword);
                formData.append('new_password', newPassword);
                
                const response = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('密码修改成功', 'success');
                    hidePasswordModal();
                } else {
                    showAlert(data.message || '修改失败', 'error');
                }
            } catch (error) {
                showAlert('网络错误，请稍后重试', 'error');
            }
        });

        // 注销登录
        async function logout() {
            if (confirm('确定要退出登录吗？')) {
                try {
                    const formData = new FormData();
                    formData.append('action', 'logout');
                    
                    await fetch('api.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    window.location.href = 'login.php';
                } catch (error) {
                    console.error('注销错误:', error);
                    window.location.href = 'login.php';
                }
            }
        }

        // 用户管理现在使用独立页面
        // 此函数已移除，用户管理功能在 user_management.php 中实现

        // 其余JavaScript代码保持不变...
        // 加载所有报告数据
        async function loadReports() {
            try {
                const response = await fetch('api.php?action=get_all_reports');
                const data = await response.json();
                if (data.success) {
                    reports = data.reports || {};
                    updateStats();
                } else if (data.need_login) {
                    window.location.href = 'login.php';
                }
            } catch (error) {
                console.error('加载报告数据失败:', error);
            }
        }

        // 更新统计信息
        function updateStats() {
            const totalReports = Object.keys(reports).length;
            const currentMonthReports = Object.keys(reports).filter(date => {
                const reportDate = new Date(date);
                return reportDate.getFullYear() === currentYear && 
                       (reportDate.getMonth() + 1) === currentMonth;
            }).length;
            
            const today = new Date();
            const currentWeek = getWeekNumber(today.getFullYear(), today.getMonth() + 1, today.getDate());
            const currentWeekReports = Object.keys(reports).filter(date => {
                const reportDate = new Date(date);
                const weekNum = getWeekNumber(reportDate.getFullYear(), reportDate.getMonth() + 1, reportDate.getDate());
                return reportDate.getFullYear() === today.getFullYear() && weekNum === currentWeek;
            }).length;
            
            document.getElementById('stats-text').innerHTML = 
                `📊 总周报: ${totalReports} | 📅 本月: ${currentMonthReports} | 📝 本周: ${currentWeekReports}`;
        }

        // 渲染日历
        function renderCalendar() {
            const monthNames = [
                '一月', '二月', '三月', '四月', '五月', '六月',
                '七月', '八月', '九月', '十月', '十一月', '十二月'
            ];
            
            document.getElementById('current-month').textContent = 
                `${currentYear}年 ${monthNames[currentMonth - 1]}`;

            const firstDay = new Date(currentYear, currentMonth - 1, 1);
            const lastDay = new Date(currentYear, currentMonth, 0);
            const daysInMonth = lastDay.getDate();
            const startDay = (firstDay.getDay() + 6) % 7;

            const calendarBody = document.getElementById('calendar-body');
            calendarBody.innerHTML = '';

            updateStats();

            let date = 1;
            const weeksInMonth = Math.ceil((daysInMonth + startDay) / 7);
            const today = new Date();
            const isCurrentMonth = today.getFullYear() === currentYear && (today.getMonth() + 1) === currentMonth;
            const todayDate = today.getDate();
            const currentWeekNumber = getWeekNumber(today.getFullYear(), today.getMonth() + 1, today.getDate());

            for (let week = 0; week < weeksInMonth; week++) {
                const row = document.createElement('tr');
                
                let weekNumber = '';
                if (week === 0) {
                    const firstCompleteWeekDay = startDay === 0 ? 1 : (8 - startDay);
                    if (firstCompleteWeekDay <= daysInMonth) {
                        weekNumber = getWeekNumber(currentYear, currentMonth, firstCompleteWeekDay);
                    } else {
                        weekNumber = getWeekNumber(currentYear, currentMonth, 1);
                    }
                } else {
                    const weekStartDay = (week * 7) - startDay + 1;
                    if (weekStartDay <= daysInMonth && weekStartDay > 0) {
                        weekNumber = getWeekNumber(currentYear, currentMonth, weekStartDay);
                    }
                }
                
                const weekCell = document.createElement('td');
                weekCell.className = 'week-number';
                if (weekNumber) {
                    weekCell.textContent = weekNumber;
                    if (weekNumber === currentWeekNumber && isCurrentMonth) {
                        weekCell.classList.add('current-week');
                    }
                }
                row.appendChild(weekCell);

                for (let dayOfWeek = 0; dayOfWeek < 7; dayOfWeek++) {
                    const cell = document.createElement('td');
                    
                    if ((week === 0 && dayOfWeek < startDay) || date > daysInMonth) {
                        cell.textContent = '';
                    } else {
                        const dateString = `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
                        cell.setAttribute('data-date', dateString);
                        
                        // 获取日历信息（根据日历类型）
                        let calendarInfo = null;
                        let displayText = '';
                        
                        try {
                            if (calendarType === 'thai') {
                                // 获取泰国佛历信息
                                calendarInfo = thaiCalendar.gregorianToThai(currentYear, currentMonth, date);
                            } else {
                                // 获取中国农历信息
                                calendarInfo = calendar.solar2lunar(currentYear, currentMonth, date);
                            }
                        } catch (error) {
                            console.warn('日历转换失败:', error);
                            calendarInfo = null;
                        }
                        
                        // 创建日期显示结构
                        const dateNumberDiv = document.createElement('div');
                        dateNumberDiv.className = 'date-number';
                        dateNumberDiv.textContent = date;
                        
                        const calendarDateDiv = document.createElement('div');
                        calendarDateDiv.className = 'lunar-date';
                        
                        // 根据日历类型显示不同信息
                        if (calendarType === 'thai' && calendarInfo) {
                            // 泰国佛历显示逻辑
                            if (calendarInfo.festival) {
                                // 显示节日
                                displayText = calendarInfo.festival;
                                calendarDateDiv.style.color = calendarInfo.buddhistFestival ? '#fd7e14' : '#dc3545';
                                calendarDateDiv.style.fontWeight = 'bold';
                                
                                // 添加节日标识
                                const festivalIndicator = document.createElement('div');
                                festivalIndicator.className = 'festival-indicator';
                                festivalIndicator.style.background = calendarInfo.buddhistFestival ? '#fd7e14' : '#dc3545';
                                festivalIndicator.textContent = calendarInfo.buddhistFestival ? '佛' : '节';
                                cell.appendChild(festivalIndicator);
                            } else {
                                // 显示泰文日期
                                displayText = `${calendarInfo.day} ${calendarInfo.monthNameShort}`;
                            }
                            calendarDateDiv.textContent = displayText;
                        } else if (calendarType === 'chinese' && calendarInfo && calendarInfo.lDay) {
                            // 中国农历显示逻辑（原有逻辑）
                            // 优先显示公历节日
                            if (calendarInfo.festival) {
                                displayText = calendarInfo.festival;
                                calendarDateDiv.style.color = '#dc3545';
                                calendarDateDiv.style.fontWeight = 'bold';
                                
                                // 添加节日标识
                                const festivalIndicator = document.createElement('div');
                                festivalIndicator.className = 'festival-indicator';
                                festivalIndicator.textContent = '节';
                                cell.appendChild(festivalIndicator);
                            }
                            // 其次显示农历节日
                            else if (calendarInfo.lunarFestival) {
                                displayText = calendarInfo.lunarFestival;
                                calendarDateDiv.style.color = '#fd7e14';
                                calendarDateDiv.style.fontWeight = 'bold';
                                
                                // 添加农历节日标识
                                const festivalIndicator = document.createElement('div');
                                festivalIndicator.className = 'festival-indicator';
                                festivalIndicator.style.background = '#fd7e14';
                                festivalIndicator.textContent = '农';
                                cell.appendChild(festivalIndicator);
                            }
                            // 农历初一显示月份
                            else if (calendarInfo.lDay === 1) {
                                displayText = calendarInfo.IMonthCn || '';
                                calendarDateDiv.style.color = '#0d6efd';
                                calendarDateDiv.style.fontWeight = 'bold';
                            }
                            // 普通日期显示农历日
                            else {
                                displayText = calendarInfo.IDayCn || '';
                            }
                            
                            calendarDateDiv.textContent = displayText;
                        } else {
                            calendarDateDiv.textContent = ''; // 如果日历信息获取失败，显示空
                        }
                        
                        cell.appendChild(dateNumberDiv);
                        cell.appendChild(calendarDateDiv);
                        
                        if (isCurrentMonth && date === todayDate) {
                            cell.classList.add('today');
                        }
                        
                        if (reports[dateString]) {
                            cell.classList.add('has-report');
                        }
                        
                        cell.addEventListener('click', () => selectDate(dateString, cell));
                        date++;
                    }
                    
                    row.appendChild(cell);
                }
                
                calendarBody.appendChild(row);
            }
        }

        // 获取ISO周数
        function getWeekNumber(year, month, day) {
            const date = new Date(year, month - 1, day);
            const targetDate = new Date(date);
            targetDate.setDate(date.getDate() + 3 - ((date.getDay() + 6) % 7));
            const yearStart = new Date(targetDate.getFullYear(), 0, 1);
            const weekNumber = Math.ceil((((targetDate - yearStart) / 86400000) + 1) / 7);
            return weekNumber;
        }

        // 获取某周的日期范围
        function getWeekRange(year, weekNumber) {
            const jan1 = new Date(year, 0, 1);
            const firstThursday = new Date(year, 0, 1 + ((11 - jan1.getDay()) % 7));
            const targetThursday = new Date(firstThursday);
            targetThursday.setDate(firstThursday.getDate() + (weekNumber - 1) * 7);
            const weekStart = new Date(targetThursday);
            weekStart.setDate(targetThursday.getDate() - 3);
            const weekEnd = new Date(targetThursday);
            weekEnd.setDate(targetThursday.getDate() + 3);
            return { start: weekStart, end: weekEnd };
        }

        // 格式化日期
        function formatDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}/${month}/${day}`;
        }

        // 切换月份
        function changeMonth(delta) {
            currentMonth += delta;
            if (currentMonth > 12) {
                currentMonth = 1;
                currentYear++;
            } else if (currentMonth < 1) {
                currentMonth = 12;
                currentYear--;
            }
            renderCalendar();
        }

        // 切换日历类型
        function changeCalendarType() {
            const select = document.getElementById('calendar-type');
            calendarType = select.value;
            renderCalendar();
            
            // 如果有选中的日期，更新周信息
            if (selectedDate) {
                updateWeekInfo(selectedDate);
            }
        }

        // 回到今天
        function goToToday() {
            const today = new Date();
            currentYear = today.getFullYear();
            currentMonth = today.getMonth() + 1;
            renderCalendar();
            
            const todayString = today.toISOString().split('T')[0];
            document.getElementById('selected_date').value = todayString;
            selectedDate = todayString;
            updateWeekInfo(todayString);
            loadReportData(todayString);
            
            setTimeout(() => {
                const todayCell = document.querySelector(`[data-date="${todayString}"]`);
                if (todayCell) {
                    selectDate(todayString, todayCell);
                }
            }, 100);
        }

        // 选择日期
        function selectDate(dateString, cell) {
            document.querySelectorAll('.calendar-table td.selected').forEach(td => {
                td.classList.remove('selected');
            });
            
            cell.classList.add('selected');
            document.getElementById('selected_date').value = dateString;
            selectedDate = dateString;
            updateWeekInfo(dateString);
            loadReportData(dateString);
        }

        // 更新周信息显示
        function updateWeekInfo(dateString) {
            const date = new Date(dateString);
            const year = date.getFullYear();
            const month = date.getMonth() + 1;
            const day = date.getDate();
            
            const weekNumber = getWeekNumber(year, month, day);
            const weekRange = getWeekRange(year, weekNumber);
            
            let weekInfo = `第${weekNumber}周 ${formatDate(weekRange.start)}-${formatDate(weekRange.end)}`;
            
            // 添加日历信息（根据日历类型）
            try {
                if (calendarType === 'thai') {
                    // 添加泰国佛历信息
                    const thaiInfo = thaiCalendar.gregorianToThai(year, month, day);
                    if (thaiInfo) {
                        weekInfo += ` | ${thaiInfo.simple} (พ.ศ.${thaiInfo.buddhistYear})`;
                        
                        // 添加节日信息
                        if (thaiInfo.festival) {
                            weekInfo += ` (${thaiInfo.festival})`;
                        }
                        
                        // 添加星期信息
                        weekInfo += ` ${thaiInfo.dayNameShort}`;
                    }
                } else {
                    // 添加中国农历信息
                    const lunarInfo = calendar.solar2lunar(year, month, day);
                    if (lunarInfo && lunarInfo.lYear) {
                        weekInfo += ` | 农历${lunarInfo.lYear}年${lunarInfo.IMonthCn}${lunarInfo.IDayCn}`;
                        
                        // 添加节日信息
                        if (lunarInfo.festival) {
                            weekInfo += ` (${lunarInfo.festival})`;
                        } else if (lunarInfo.lunarFestival) {
                            weekInfo += ` (${lunarInfo.lunarFestival})`;
                        }
                        
                        // 添加生肖年份
                        weekInfo += ` ${lunarInfo.Animal}年`;
                    }
                }
            } catch (error) {
                console.warn('获取日历信息失败:', error);
            }
            
            document.getElementById('week-info').innerHTML = weekInfo;
        }

        // 加载报告数据
        async function loadReportData(dateString) {
            if (reports[dateString]) {
                const report = reports[dateString];
                document.getElementById('work_content').value = report.work_content || '';
                document.getElementById('next_plan').value = report.next_plan || '';
                document.getElementById('issues').value = report.issues || '';
                document.getElementById('suggestions').value = report.suggestions || '';
            } else {
                document.getElementById('work_content').value = '';
                document.getElementById('next_plan').value = '';
                document.getElementById('issues').value = '';
                document.getElementById('suggestions').value = '';
            }
        }

        // 设置默认日期
        function setDefaultDate() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('selected_date').value = today;
            selectedDate = today;
            updateWeekInfo(today);
        }

        // 监听日期输入框的变化
        document.getElementById('selected_date').addEventListener('change', function(e) {
            const newDate = e.target.value;
            selectedDate = newDate;
            updateWeekInfo(newDate);
            loadReportData(newDate);
        });

        // 保存报告
        document.getElementById('reportForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'save_report');
            
            try {
                showAlert('正在保存...', 'success');
                
                const response = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('周报保存成功！', 'success');
                    // 重新加载数据以更新日历显示
                    await loadReports();
                    renderCalendar();
                } else if (data.need_login) {
                    window.location.href = 'login.php';
                } else {
                    showAlert(data.message || '保存失败', 'error');
                }
            } catch (error) {
                console.error('保存错误:', error);
                showAlert('保存失败：' + error.message, 'error');
            }
        });

        // 显示提示消息
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alert-container');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.textContent = message;
            
            alertContainer.appendChild(alert);
            
            setTimeout(() => {
                alert.remove();
            }, 3000);
        }

        // 导出到Excel
        async function exportToExcel() {
            try {
                if (Object.keys(reports).length === 0) {
                    showAlert('没有数据可以导出', 'error');
                    return;
                }

                let csvContent = '\uFEFF';
                csvContent += '日期,姓名,本周工作内容,下周工作计划,遇到的问题,建议与改进\n';
                
                const sortedDates = Object.keys(reports).sort();
                
                for (const date of sortedDates) {
                    const report = reports[date];
                    const row = [
                        date,
                        `"${(report.reporter_name || '').replace(/"/g, '""')}"`,
                        `"${(report.work_content || '').replace(/"/g, '""')}"`,
                        `"${(report.next_plan || '').replace(/"/g, '""')}"`,
                        `"${(report.issues || '').replace(/"/g, '""')}"`,
                        `"${(report.suggestions || '').replace(/"/g, '""')}"`
                    ].join(',');
                    csvContent += row + '\n';
                }

                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', `森森信息部工作周报表_${new Date().toISOString().split('T')[0]}.csv`);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                showAlert('CSV导出成功！', 'success');
            } catch (error) {
                showAlert('导出失败：' + error.message, 'error');
            }
        }

        // 导出到PDF
        async function exportToPDF() {
            try {
                if (Object.keys(reports).length === 0) {
                    showAlert('没有数据可以导出', 'error');
                    return;
                }

                showAlert('正在生成PDF，请稍候...', 'success');
                window.open('pdf_export.php?action=export_pdf', '_blank');
                
            } catch (error) {
                showAlert('PDF导出失败：' + error.message, 'error');
            }
        }
    </script>
</body>
</html>
