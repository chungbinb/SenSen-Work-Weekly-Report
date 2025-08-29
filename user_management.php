<?php
require_once 'config.php';
require_once 'auth.php';

$auth = new Auth($pdo);

// 检查登录状态和管理员权限
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user = $auth->getCurrentUser();
if ($user['role'] !== 'admin') {
    header('Location: main.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户管理 - 森森信息部工作周报表</title>
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
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 30px;
            overflow: hidden;
        }

        .header-content {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left h1 {
            font-size: 2rem;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .header-left p {
            opacity: 0.9;
        }

        .back-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }

        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h2 {
            font-size: 1.5rem;
            margin: 0;
        }

        .card-body {
            padding: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s ease;
            margin-right: 10px;
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

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .btn-small {
            padding: 6px 12px;
            font-size: 12px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }

        .table tbody tr:hover {
            background: #f8f9fa;
        }

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
        .form-select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border-radius: 15px;
            width: 500px;
            max-width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }

        .modal-header h3 {
            margin: 0;
            color: #495057;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }

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

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .role-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .role-admin {
            background: #667eea;
            color: white;
        }

        .role-user {
            background: #6c757d;
            color: white;
        }

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

        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }

        .no-data i {
            font-size: 3rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .modal-content {
                width: 95%;
                padding: 20px;
            }
            
            .table {
                font-size: 12px;
            }
            
            .table th,
            .table td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <div class="header-left">
                    <h1>👥 用户管理</h1>
                    <p>管理系统用户和权限</p>
                </div>
                <a href="main.php" class="back-btn">← 返回主页</a>
            </div>
        </div>

        <div id="alert-container"></div>

        <!-- 用户列表 -->
        <div class="card">
            <div class="card-header">
                <h2>用户列表</h2>
                <button class="btn btn-primary" onclick="showAddUserModal()">+ 添加用户</button>
            </div>
            <div class="card-body">
                <div class="loading" id="loading">
                    <div class="spinner"></div>
                    <p>加载中...</p>
                </div>
                
                <div id="users-table-container">
                    <table class="table" id="users-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>用户名</th>
                                <th>姓名</th>
                                <th>角色</th>
                                <th>状态</th>
                                <th>创建时间</th>
                                <th>最后登录</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody id="users-tbody">
                        </tbody>
                    </table>
                </div>

                <div class="no-data" id="no-data" style="display: none;">
                    <i>👥</i>
                    <h3>暂无用户数据</h3>
                    <p>点击"添加用户"按钮创建第一个用户</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 添加/编辑用户模态框 -->
    <div class="modal" id="userModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-title">添加用户</h3>
            </div>
            <form id="userForm">
                <input type="hidden" id="user-id" name="user_id">
                
                <div class="form-group">
                    <label for="username" class="form-label">用户名</label>
                    <input type="text" class="form-input" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="name" class="form-label">姓名</label>
                    <input type="text" class="form-input" id="name" name="name" required>
                </div>
                
                <div class="form-group" id="password-group">
                    <label for="password" class="form-label">密码</label>
                    <input type="password" class="form-input" id="password" name="password" required>
                    <small style="color: #6c757d; margin-top: 5px; display: block;">密码长度至少6位</small>
                </div>
                
                <div class="form-group">
                    <label for="role" class="form-label">角色</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="user">普通用户</option>
                        <option value="admin">管理员</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="status" class="form-label">状态</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="active">激活</option>
                        <option value="inactive">停用</option>
                    </select>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="hideUserModal()">取消</button>
                    <button type="submit" class="btn btn-primary" id="submit-btn">保存</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 重置密码模态框 -->
    <div class="modal" id="resetPasswordModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>重置密码</h3>
            </div>
            <form id="resetPasswordForm">
                <input type="hidden" id="reset-user-id" name="user_id">
                
                <div class="form-group">
                    <label for="new-password" class="form-label">新密码</label>
                    <input type="password" class="form-input" id="new-password" name="new_password" required>
                    <small style="color: #6c757d; margin-top: 5px; display: block;">密码长度至少6位</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm-password" class="form-label">确认密码</label>
                    <input type="password" class="form-input" id="confirm-password" name="confirm_password" required>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="hideResetPasswordModal()">取消</button>
                    <button type="submit" class="btn btn-primary">重置密码</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let users = [];
        let editingUserId = null;

        // 初始化
        document.addEventListener('DOMContentLoaded', function() {
            loadUsers();
        });

        // 加载用户列表
        async function loadUsers() {
            showLoading(true);
            try {
                const response = await fetch('api.php?action=get_users');
                const data = await response.json();
                
                if (data.success) {
                    users = data.users || [];
                    renderUsersTable();
                } else if (data.need_login) {
                    window.location.href = 'login.php';
                } else {
                    showAlert(data.message || '加载用户列表失败', 'error');
                }
            } catch (error) {
                console.error('加载用户列表失败:', error);
                showAlert('网络错误，请稍后重试', 'error');
            } finally {
                showLoading(false);
            }
        }

        // 渲染用户表格
        function renderUsersTable() {
            const tbody = document.getElementById('users-tbody');
            const noData = document.getElementById('no-data');
            const tableContainer = document.getElementById('users-table-container');
            
            if (users.length === 0) {
                tableContainer.style.display = 'none';
                noData.style.display = 'block';
                return;
            }
            
            tableContainer.style.display = 'block';
            noData.style.display = 'none';
            
            tbody.innerHTML = '';
            
            users.forEach(user => {
                const row = document.createElement('tr');
                
                const lastLogin = user.last_login 
                    ? new Date(user.last_login).toLocaleString('zh-CN')
                    : '从未登录';
                
                const createdAt = new Date(user.created_at).toLocaleString('zh-CN');
                
                row.innerHTML = `
                    <td>${user.id}</td>
                    <td>${escapeHtml(user.username)}</td>
                    <td>${escapeHtml(user.name)}</td>
                    <td><span class="role-badge role-${user.role}">${user.role === 'admin' ? '管理员' : '普通用户'}</span></td>
                    <td><span class="status-badge status-${user.status}">${user.status === 'active' ? '激活' : '停用'}</span></td>
                    <td>${createdAt}</td>
                    <td>${lastLogin}</td>
                    <td>
                        <button class="btn btn-small btn-primary" onclick="editUser(${user.id})">编辑</button>
                        <button class="btn btn-small btn-success" onclick="resetPassword(${user.id})">重置密码</button>
                        ${user.id !== 1 ? `<button class="btn btn-small btn-danger" onclick="deleteUser(${user.id})">删除</button>` : ''}
                    </td>
                `;
                
                tbody.appendChild(row);
            });
        }

        // 显示/隐藏加载状态
        function showLoading(show) {
            document.getElementById('loading').style.display = show ? 'block' : 'none';
        }

        // 显示添加用户模态框
        function showAddUserModal() {
            editingUserId = null;
            document.getElementById('modal-title').textContent = '添加用户';
            document.getElementById('userForm').reset();
            document.getElementById('user-id').value = '';
            document.getElementById('password-group').style.display = 'block';
            document.getElementById('password').required = true;
            document.getElementById('submit-btn').textContent = '添加用户';
            document.getElementById('userModal').style.display = 'block';
        }

        // 编辑用户
        function editUser(userId) {
            const user = users.find(u => u.id === userId);
            if (!user) return;
            
            editingUserId = userId;
            document.getElementById('modal-title').textContent = '编辑用户';
            document.getElementById('user-id').value = user.id;
            document.getElementById('username').value = user.username;
            document.getElementById('name').value = user.name;
            document.getElementById('role').value = user.role;
            document.getElementById('status').value = user.status;
            
            // 编辑时不显示密码字段
            document.getElementById('password-group').style.display = 'none';
            document.getElementById('password').required = false;
            document.getElementById('submit-btn').textContent = '保存修改';
            document.getElementById('userModal').style.display = 'block';
        }

        // 隐藏用户模态框
        function hideUserModal() {
            document.getElementById('userModal').style.display = 'none';
            document.getElementById('userForm').reset();
            editingUserId = null;
        }

        // 重置密码
        function resetPassword(userId) {
            document.getElementById('reset-user-id').value = userId;
            document.getElementById('resetPasswordForm').reset();
            document.getElementById('resetPasswordModal').style.display = 'block';
        }

        // 隐藏重置密码模态框
        function hideResetPasswordModal() {
            document.getElementById('resetPasswordModal').style.display = 'none';
            document.getElementById('resetPasswordForm').reset();
        }

        // 删除用户
        async function deleteUser(userId) {
            const user = users.find(u => u.id === userId);
            if (!user) return;
            
            if (!confirm(`确定要删除用户"${user.name}"吗？此操作不可恢复！`)) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'delete_user');
                formData.append('user_id', userId);
                
                const response = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('用户删除成功', 'success');
                    loadUsers();
                } else {
                    showAlert(data.message || '删除失败', 'error');
                }
            } catch (error) {
                console.error('删除用户失败:', error);
                showAlert('网络错误，请稍后重试', 'error');
            }
        }

        // 提交用户表单
        document.getElementById('userForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const action = editingUserId ? 'update_user' : 'add_user';
            formData.append('action', action);
            
            // 验证密码
            if (!editingUserId) {
                const password = formData.get('password');
                if (password.length < 6) {
                    showAlert('密码长度不能少于6位', 'error');
                    return;
                }
            }
            
            try {
                const response = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert(editingUserId ? '用户更新成功' : '用户添加成功', 'success');
                    hideUserModal();
                    loadUsers();
                } else {
                    showAlert(data.message || '操作失败', 'error');
                }
            } catch (error) {
                console.error('操作失败:', error);
                showAlert('网络错误，请稍后重试', 'error');
            }
        });

        // 提交重置密码表单
        document.getElementById('resetPasswordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const newPassword = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            
            if (newPassword !== confirmPassword) {
                showAlert('两次输入的密码不一致', 'error');
                return;
            }
            
            if (newPassword.length < 6) {
                showAlert('密码长度不能少于6位', 'error');
                return;
            }
            
            const formData = new FormData(this);
            formData.append('action', 'reset_user_password');
            
            try {
                const response = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('密码重置成功', 'success');
                    hideResetPasswordModal();
                } else {
                    showAlert(data.message || '重置失败', 'error');
                }
            } catch (error) {
                console.error('重置密码失败:', error);
                showAlert('网络错误，请稍后重试', 'error');
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

        // HTML转义
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // 点击模态框外部关闭
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                hideUserModal();
                hideResetPasswordModal();
            }
        });
    </script>
</body>
</html>
