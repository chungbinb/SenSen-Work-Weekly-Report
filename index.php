

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
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
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
            padding: 12px 8px;
            border: 1px solid #e9ecef;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
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
            background: #d4edda;
            font-weight: bold;
            color: #155724;
        }

        .calendar-table td.has-report::after {
            content: '📝';
            position: absolute;
            top: 2px;
            right: 2px;
            font-size: 12px;
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
            .main-content {
                gap: 20px;
            }
            
            .header h1 {
                font-size: 2rem;
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
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📅 森森信息部工作周报表</h1>
            <p>高效管理您的工作周报，支持在线编辑和导出功能</p>
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
                    <div style="text-align: center; margin-bottom: 15px;">
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
                            <label for="reporter_name" class="form-label">姓名</label>
                            <input type="text" class="form-input" id="reporter_name" name="reporter_name" placeholder="请输入您的姓名" required>
                        </div>
                        
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

    <script>
        let currentYear = new Date().getFullYear();
        let currentMonth = new Date().getMonth() + 1;
        let reports = {};
        let selectedDate = '';

        // 初始化
        document.addEventListener('DOMContentLoaded', function() {
            loadReports();
            renderCalendar();
            setDefaultDate();
        });

        // 加载所有报告数据
        async function loadReports() {
            try {
                const response = await fetch('api.php?action=get_all_reports');
                const data = await response.json();
                if (data.success) {
                    reports = data.reports || {};
                    updateStats();
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
            // 调整startDay：将周日(0)调整为6，周一(1)调整为0，以此类推
            const startDay = (firstDay.getDay() + 6) % 7;

            const calendarBody = document.getElementById('calendar-body');
            calendarBody.innerHTML = '';

            // 更新统计信息
            updateStats();

            let date = 1;
            const weeksInMonth = Math.ceil((daysInMonth + startDay) / 7);
            const today = new Date();
            const isCurrentMonth = today.getFullYear() === currentYear && (today.getMonth() + 1) === currentMonth;
            const todayDate = today.getDate();
            const currentWeekNumber = getWeekNumber(today.getFullYear(), today.getMonth() + 1, today.getDate());

            for (let week = 0; week < weeksInMonth; week++) {
                const row = document.createElement('tr');
                
                // 计算这一行应该显示的周数
                let weekNumber = '';
                if (week === 0) {
                    // 第一周：如果月初不是周一，取本月第一个完整周的周数
                    const firstCompleteWeekDay = startDay === 0 ? 1 : (8 - startDay);
                    if (firstCompleteWeekDay <= daysInMonth) {
                        weekNumber = getWeekNumber(currentYear, currentMonth, firstCompleteWeekDay);
                    } else {
                        weekNumber = getWeekNumber(currentYear, currentMonth, 1);
                    }
                } else {
                    // 其他周：取该周第一天的周数
                    const weekStartDay = (week * 7) - startDay + 1;
                    if (weekStartDay <= daysInMonth && weekStartDay > 0) {
                        weekNumber = getWeekNumber(currentYear, currentMonth, weekStartDay);
                    }
                }
                
                // 周数列
                const weekCell = document.createElement('td');
                weekCell.className = 'week-number';
                if (weekNumber) {
                    weekCell.textContent = weekNumber;
                    // 高亮当前周
                    if (weekNumber === currentWeekNumber && isCurrentMonth) {
                        weekCell.classList.add('current-week');
                    }
                }
                row.appendChild(weekCell);

                // 日期列
                for (let dayOfWeek = 0; dayOfWeek < 7; dayOfWeek++) {
                    const cell = document.createElement('td');
                    
                    if ((week === 0 && dayOfWeek < startDay) || date > daysInMonth) {
                        cell.textContent = '';
                    } else {
                        cell.textContent = date;
                        const dateString = `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
                        cell.setAttribute('data-date', dateString);
                        
                        // 高亮今天
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

        // 获取ISO周数（今年的第几周）- 使用标准ISO 8601计算方式
        function getWeekNumber(year, month, day) {
            const date = new Date(year, month - 1, day);
            
            // 设置为星期四来确保正确的年份归属
            const targetDate = new Date(date);
            targetDate.setDate(date.getDate() + 3 - ((date.getDay() + 6) % 7));
            
            // 获取目标年份的1月1日
            const yearStart = new Date(targetDate.getFullYear(), 0, 1);
            
            // 计算周数
            const weekNumber = Math.ceil((((targetDate - yearStart) / 86400000) + 1) / 7);
            
            return weekNumber;
        }

        // 获取某年有多少周
        function getWeeksInYear(year) {
            const dec31 = new Date(year, 11, 31);
            const weekNumber = getSimpleWeekNumber(dec31);
            return weekNumber >= 52 ? weekNumber : 52;
        }

        // 简单的周数计算（用于辅助计算）
        function getSimpleWeekNumber(date) {
            const yearStart = new Date(date.getFullYear(), 0, 1);
            const weekNumber = Math.ceil((((date - yearStart) / 86400000) + yearStart.getDay() + 1) / 7);
            return weekNumber;
        }

        // 获取某周的日期范围（ISO标准：周一到周日）
        function getWeekRange(year, weekNumber) {
            // 创建该年的1月1日
            const jan1 = new Date(year, 0, 1);
            
            // 找到第一周的星期四（ISO标准）
            const firstThursday = new Date(year, 0, 1 + ((11 - jan1.getDay()) % 7));
            
            // 计算指定周的星期四
            const targetThursday = new Date(firstThursday);
            targetThursday.setDate(firstThursday.getDate() + (weekNumber - 1) * 7);
            
            // 计算该周的星期一（开始）
            const weekStart = new Date(targetThursday);
            weekStart.setDate(targetThursday.getDate() - 3);
            
            // 计算该周的星期日（结束）
            const weekEnd = new Date(targetThursday);
            weekEnd.setDate(targetThursday.getDate() + 3);
            
            return { start: weekStart, end: weekEnd };
        }

        // 格式化日期为 YYYY/MM/DD
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

        // 回到今天
        function goToToday() {
            const today = new Date();
            currentYear = today.getFullYear();
            currentMonth = today.getMonth() + 1;
            renderCalendar();
            
            // 自动选择今天的日期
            const todayString = today.toISOString().split('T')[0];
            document.getElementById('selected_date').value = todayString;
            selectedDate = todayString;
            updateWeekInfo(todayString);
            loadReportData(todayString);
            
            // 高亮今天的单元格
            setTimeout(() => {
                const todayCell = document.querySelector(`[data-date="${todayString}"]`);
                if (todayCell) {
                    selectDate(todayString, todayCell);
                }
            }, 100);
        }

        // 选择日期
        function selectDate(dateString, cell) {
            // 移除之前选中的样式
            document.querySelectorAll('.calendar-table td.selected').forEach(td => {
                td.classList.remove('selected');
            });
            
            // 添加选中样式
            cell.classList.add('selected');
            
            // 设置表单中的日期
            document.getElementById('selected_date').value = dateString;
            selectedDate = dateString;
            
            // 更新周信息
            updateWeekInfo(dateString);
            
            // 加载该日期的报告数据
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
            
            const weekInfo = `第${weekNumber}周 ${formatDate(weekRange.start)}-${formatDate(weekRange.end)}`;
            document.getElementById('week-info').textContent = weekInfo;
        }

        // 加载报告数据
        async function loadReportData(dateString) {
            if (reports[dateString]) {
                const report = reports[dateString];
                document.getElementById('reporter_name').value = report.reporter_name || '';
                document.getElementById('work_content').value = report.work_content || '';
                document.getElementById('next_plan').value = report.next_plan || '';
                document.getElementById('issues').value = report.issues || '';
                document.getElementById('suggestions').value = report.suggestions || '';
            } else {
                // 清空表单
                document.getElementById('reporter_name').value = '';
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
            
            console.log('表单提交开始');
            console.log('选中的日期:', selectedDate);
            
            const formData = new FormData(this);
            formData.append('action', 'save_report');
            
            // 添加调试信息
            console.log('FormData内容:');
            for (let [key, value] of formData.entries()) {
                console.log(key, value);
            }
            
            try {
                showAlert('正在保存...', 'success');
                
                const response = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });
                
                console.log('响应状态:', response.status);
                console.log('响应头:', response.headers);
                
                const responseText = await response.text();
                console.log('原始响应:', responseText);
                
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('JSON解析错误:', parseError);
                    throw new Error('服务器响应格式错误: ' + responseText);
                }
                
                if (data.success) {
                    showAlert('周报保存成功！', 'success');
                    // 更新本地数据
                    reports[selectedDate] = {
                        date: selectedDate,
                        reporter_name: formData.get('reporter_name'),
                        work_content: formData.get('work_content'),
                        next_plan: formData.get('next_plan'),
                        issues: formData.get('issues'),
                        suggestions: formData.get('suggestions')
                    };
                    // 重新渲染日历以显示新的报告标记和更新统计
                    renderCalendar();
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

                // 创建CSV数据
                let csvContent = '\uFEFF'; // BOM for UTF-8
                csvContent += '日期,姓名,本周工作内容,下周工作计划,遇到的问题,建议与改进\n';
                
                // 按日期排序
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

                // 创建下载链接
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
                
                // 直接跳转到PDF导出URL
                window.open('pdf_export.php?action=export_pdf', '_blank');
                
            } catch (error) {
                showAlert('PDF导出失败：' + error.message, 'error');
            }
        }
    </script>
</body>
</html>
