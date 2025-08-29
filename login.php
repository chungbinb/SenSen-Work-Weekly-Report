<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - 森森信息部工作周报表</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
            position: relative;
        }

        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }

        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(255,255,255,0.1) 10px,
                rgba(255,255,255,0.1) 20px
            );
            animation: move 20s linear infinite;
        }

        @keyframes move {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        .login-header h1 {
            font-size: 1.8rem;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .login-header p {
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .login-form {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .form-input::placeholder {
            color: #adb5bd;
        }

        .form-group .icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 20px;
        }

        .form-input.with-icon {
            padding-left: 50px;
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .login-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .alert {
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid transparent;
            animation: slideInDown 0.3s ease;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        .loading {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.9);
            border-radius: 12px;
            z-index: 10;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        .footer {
            text-align: center;
            padding: 20px 30px;
            color: #6c757d;
            font-size: 14px;
            border-top: 1px solid #e9ecef;
        }

        .demo-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            color: #856404;
            font-size: 14px;
        }

        .demo-info strong {
            color: #533f03;
        }

        /* 响应式设计 */
        @media (max-width: 480px) {
            .login-container {
                margin: 10px;
            }
            
            .login-header {
                padding: 30px 20px;
            }
            
            .login-form {
                padding: 30px 20px;
            }
            
            .login-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>📅 工作周报表</h1>
            <p>森森信息部工作周报管理系统</p>
        </div>
        
        <div class="login-form">
            <div class="demo-info">
                <strong>默认账号信息：</strong><br>
                用户名: admin<br>
                密码: 123456
            </div>
            
            <div id="alert-container"></div>
            
            <form id="loginForm">
                <div class="form-group">
                    <span class="icon">👤</span>
                    <input type="text" class="form-input with-icon" id="username" name="username" placeholder="请输入用户名" required>
                </div>
                
                <div class="form-group">
                    <span class="icon">🔒</span>
                    <input type="password" class="form-input with-icon" id="password" name="password" placeholder="请输入密码" required>
                </div>
                
                <button type="submit" class="login-btn" id="loginBtn">
                    登录系统
                    <div class="loading" id="loading">
                        <div class="spinner"></div>
                    </div>
                </button>
            </form>
        </div>
        
        <div class="footer">
            <p>© 2024 森森信息部 | 工作周报表系统 v2.0</p>
        </div>
    </div>

    <script>
        // 检查是否已经登录
        document.addEventListener('DOMContentLoaded', function() {
            checkLoginStatus();
        });

        function checkLoginStatus() {
            fetch('api.php?action=check_login')
                .then(response => {
                    console.log('Check login response status:', response.status);
                    return response.text();
                })
                .then(text => {
                    console.log('Check login response text:', text);
                    try {
                        const data = JSON.parse(text);
                        if (data.success && data.logged_in) {
                            // 已登录，跳转到主页
                            window.location.href = 'main.php';
                        }
                    } catch (parseError) {
                        console.error('检查登录状态JSON解析错误:', parseError);
                        console.error('响应内容:', text);
                        // 不显示错误给用户，只在控制台记录
                    }
                })
                .catch(error => {
                    console.error('检查登录状态失败:', error);
                });
        }

        // 登录表单提交
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            e.stopPropagation();  // 阻止事件冒泡
            
            await handleLogin();
        });
        
        async function handleLogin() {
            
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            
            if (!username || !password) {
                showAlert('请输入用户名和密码', 'error');
                return;
            }
            
            const loginBtn = document.getElementById('loginBtn');
            const loading = document.getElementById('loading');
            
            // 显示加载状态
            loginBtn.disabled = true;
            loading.style.display = 'block';
            
            try {
                const formData = new FormData();
                formData.append('action', 'login');
                formData.append('username', username);
                formData.append('password', password);
                
                const response = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });
                
                console.log('Login response status:', response.status);
                const responseText = await response.text();
                console.log('Login response text:', responseText);
                
                try {
                    const data = JSON.parse(responseText);
                    
                    if (data.success) {
                        showAlert('登录成功，正在跳转...', 'success');
                        // 延迟跳转，让用户看到成功消息
                        setTimeout(() => {
                            window.location.href = 'main.php';
                        }, 1000);
                    } else {
                        showAlert(data.message || '登录失败', 'error');
                    }
                } catch (parseError) {
                    console.error('JSON解析错误:', parseError);
                    console.error('响应内容:', responseText);
                    showAlert('服务器响应格式错误，请检查服务器配置', 'error');
                }
            } catch (error) {
                console.error('网络请求错误:', error);
                showAlert('网络错误，请稍后重试', 'error');
            } finally {
                // 隐藏加载状态
                loginBtn.disabled = false;
                loading.style.display = 'none';
            }
        }

        // 显示提示消息
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alert-container');
            
            // 清除现有的提示
            alertContainer.innerHTML = '';
            
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.textContent = message;
            
            alertContainer.appendChild(alert);
            
            // 3秒后自动消失
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 3000);
        }

        // 回车键快捷登录
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('loginForm').dispatchEvent(new Event('submit'));
            }
        });
    </script>
</body>
</html>
