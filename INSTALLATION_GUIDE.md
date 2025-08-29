# 森森信息部工作周报表系统 - 安装指南

## 📋 系统概述

这是一个基于PHP的工作周报表管理系统，集成了中国农历和泰国佛历双日历功能。系统支持多用户管理、智能日历显示、周报编写、数据导出等完整功能。

## 🛠️ 环境要求

### 基础环境
- **PHP版本**：≥ 7.4
- **数据库**：MySQL 5.7+ 或 MariaDB 10.2+
- **Web服务器**：Apache 2.4+ 或 Nginx 1.18+
- **磁盘空间**：至少50MB

### PHP扩展要求
- `pdo` - 数据库连接
- `pdo_mysql` - MySQL数据库支持
- `session` - 会话管理
- `json` - JSON数据处理
- `mbstring` - 多字节字符串处理

## 📦 快速安装

### 方式一：使用安装向导（推荐）

1. **下载系统文件**
   ```bash
   # 将所有系统文件上传到Web服务器目录
   # 确保保留文件夹结构
   ```

2. **设置文件权限**
   ```bash
   chmod 755 /path/to/your/website
   chmod 644 *.php *.html *.js *.css *.md
   chmod 666 reports.json  # 如果存在
   ```

3. **创建数据库**
   ```sql
   -- 登录MySQL/MariaDB
   CREATE DATABASE work_report CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'work_user'@'localhost' IDENTIFIED BY 'your_password';
   GRANT ALL PRIVILEGES ON work_report.* TO 'work_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

4. **运行安装向导**
   - 在浏览器中访问：`http://yourdomain.com/install.php`
   - 按照向导提示完成安装：
     - **步骤1**：欢迎页面和系统介绍
     - **步骤2**：数据库配置
     - **步骤3**：自动创建表结构和默认账号

5. **安装完成**
   - 系统将自动创建默认管理员账号
   - 删除 `install.php` 文件（安全建议）

### 方式二：手动安装

1. **配置数据库连接**
   
   编辑 `config.php` 文件：
   ```php
   $config = [
       'host' => 'localhost',
       'port' => 3306,
       'dbname' => 'work_report',
       'username' => 'work_user',
       'password' => 'your_password',
       'charset' => 'utf8mb4'
   ];
   ```

2. **导入数据库结构**
   ```sql
   -- 创建用户表
   CREATE TABLE `users` (
     `id` int(11) NOT NULL AUTO_INCREMENT,
     `username` varchar(50) NOT NULL,
     `password` varchar(255) NOT NULL,
     `name` varchar(100) NOT NULL,
     `role` enum('admin','user') DEFAULT 'user',
     `status` enum('active','inactive') DEFAULT 'active',
     `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
     `last_login` timestamp NULL DEFAULT NULL,
     PRIMARY KEY (`id`),
     UNIQUE KEY `username` (`username`)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

   -- 创建报告表
   CREATE TABLE `reports` (
     `id` int(11) NOT NULL AUTO_INCREMENT,
     `user_id` int(11) NOT NULL,
     `report_date` date NOT NULL,
     `work_content` text,
     `next_plan` text,
     `issues` text,
     `suggestions` text,
     `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
     `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     PRIMARY KEY (`id`),
     UNIQUE KEY `user_date` (`user_id`,`report_date`),
     KEY `user_id` (`user_id`),
     CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
   ```

3. **创建默认管理员账号**
   ```sql
   INSERT INTO users (username, password, name, role, status) VALUES 
   ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '系统管理员', 'admin', 'active');
   -- 密码是：123456
   ```

## 🔐 默认账号信息

### 系统管理员账号
- **用户名**：`admin`
- **密码**：`123456`
- **权限**：管理员（可管理所有用户和数据）

### 测试用户账号（在线演示）
- **用户名**：`12345`
- **密码**：`123456`
- **权限**：普通用户

> ⚠️ **重要安全提示**：
> 1. 安装完成后**必须立即修改**默认管理员密码
> 2. 生产环境中请删除或重命名 `install.php` 文件
> 3. 建议为数据库用户设置强密码
> 4. 定期备份数据库数据

## 🎯 首次登录步骤

1. **访问系统**
   - 打开浏览器访问：`http://yourdomain.com`
   - 系统将自动跳转到登录页面

2. **使用默认账号登录**
   ```
   用户名：admin
   密码：123456
   ```

3. **修改默认密码**
   - 登录后点击右上角用户头像
   - 选择"修改密码"
   - 输入新密码（建议至少8位，包含字母数字特殊字符）

4. **系统初始化**
   - 创建其他用户账号（如需要）
   - 配置系统设置
   - 开始使用周报功能

## 📂 目录结构

```
工作周报表/
├── api.php              # API接口文件
├── auth.php             # 用户认证类
├── config.php           # 数据库配置
├── export.php           # 数据导出功能
├── index.php            # 系统首页
├── install.php          # 安装向导（安装后请删除）
├── login.php            # 登录页面
├── main.php             # 主应用界面
├── user_management.php  # 用户管理界面
├── pdf_export.php       # PDF导出功能
├── upgrade_db.php       # 数据库升级脚本
├── lunar.js             # 中国农历库
├── thai.js              # 泰国佛历库
├── reports.json         # 历史数据文件（如存在）
└── 文档/
    ├── README.md                # 项目说明
    ├── THAI_CALENDAR_UPDATE.md  # 泰历功能文档
    ├── PROJECT_SUMMARY.md       # 项目总结
    └── LUNAR_UPDATE.md          # 农历功能文档
```

## 🌟 核心功能

### 双日历系统
- **中国农历**：农历日期、传统节日、生肖年份、干支纪年
- **泰国佛历**：佛历年份（佛历2567年=公历2024年）、泰语月份、佛教节日

### 用户管理
- 管理员可创建、编辑、删除用户
- 用户角色分为管理员和普通用户
- 支持密码重置和账号状态管理

### 周报功能
- 基于日历的周报编写界面
- 自动计算ISO周数和周日期范围
- 支持工作内容、下周计划、问题建议等字段

### 数据导出
- **CSV导出**：表格格式，便于Excel处理
- **PDF导出**：专业格式，适合打印和分享

## 🔧 配置选项

### 数据库配置
编辑 `config.php`：
```php
// 时区设置
date_default_timezone_set('Asia/Shanghai');

// 数据库配置
$config = [
    'host' => 'localhost',      # 数据库主机
    'port' => 3306,             # 数据库端口
    'dbname' => 'work_report',  # 数据库名称
    'username' => 'work_user',  # 数据库用户名
    'password' => 'your_pass',  # 数据库密码
    'charset' => 'utf8mb4'      # 字符集
];
```

### 应用配置
```php
// 应用信息
define('APP_NAME', '森森信息部工作周报表');
define('VERSION', '2.0');

// 错误报告（生产环境建议关闭）
ini_set('display_errors', 0);
ini_set('log_errors', 1);
```

## 🚨 故障排除

### 常见问题

**1. 无法访问install.php**
- 检查文件是否存在于Web根目录
- 确认Web服务器配置正确
- 检查文件权限设置

**2. 数据库连接失败**
- 验证数据库服务是否运行
- 检查数据库连接参数
- 确认数据库用户权限

**3. 登录后显示空白页面**
- 检查PHP错误日志
- 确认所有必需的PHP扩展已安装
- 验证数据库表结构是否正确

**4. 无法导出PDF**
- 检查服务器是否支持文件下载
- 确认浏览器没有阻止下载
- 查看PHP错误日志

### 日志文件位置
- **应用错误日志**：`error.log`（项目根目录）
- **Web服务器日志**：通常在 `/var/log/nginx/` 或 `/var/log/apache2/`
- **PHP错误日志**：根据php.ini配置

## 📞 技术支持

### 系统信息
- **版本**：2.0
- **开发语言**：PHP 7.4+, JavaScript ES6+, CSS3
- **数据库**：MySQL/MariaDB
- **架构**：MVC模式，前后端分离API

### 更新说明
- ✅ 集成泰国佛历功能
- ✅ 双日历系统切换
- ✅ 用户管理界面优化
- ✅ PDF导出功能增强
- ✅ 安全性改进

---

## 🎉 安装完成

恭喜！您已成功安装森森信息部工作周报表系统。

**下一步操作：**
1. 使用默认账号登录系统
2. 立即修改管理员密码
3. 根据需要创建其他用户账号
4. 开始使用双日历周报功能
5. 删除 `install.php` 文件确保系统安全

**在线预览：** [job.i-522.com](http://job.i-522.com)（测试账号：12345/123456）

感谢使用本系统！🚀
