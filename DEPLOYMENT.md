# 项目部署说明

## 功能特性

- ✅ 服务器端数据存储
- ✅ 多用户支持（可扩展）
- ✅ 数据持久化
- ✅ CSV导出
- ✅ RESTful API
- ✅ 现代化响应式界面
- ✅ 零外部依赖
- ✅ ISO标准周数显示（今年第几周）
- ✅ 智能周范围计算和显示

## 文件列表
- `index.php` - 主页面（前端界面）
- `api.php` - 后端API接口
- `config.php` - 配置文件
- `export.php` - CSV导出功能

## 部署要求
- PHP 7.4+
- Web服务器（Apache/Nginx/PHP内置服务器）

## 部署步骤
```bash
# 1. 将所有PHP文件上传到Web服务器目录
# 2. 确保目录有读写权限
# 3. 启动Web服务器
php -S localhost:8000  # 使用PHP内置服务器

# 4. 访问 http://localhost:8000
```

## 使用场景

适用于：
- 正式的工作环境
- 需要数据持久化的场景
- 多人协作使用
- 企业内部系统
- 个人工作管理

## 数据格式

两个版本使用相同的数据格式：

```json
{
  "2023-12-25": {
    "date": "2023-12-25",
    "work_content": "本周工作内容",
    "next_plan": "下周工作计划", 
    "issues": "遇到的问题",
    "suggestions": "建议与改进",
    "created_at": "2023-12-25T10:30:00.000Z",
    "updated_at": "2023-12-25T15:45:00.000Z"
  }
}
```

## 技术特点

### 前端技术
- 纯HTML5 + CSS3 + JavaScript
- 无外部依赖
- 响应式设计
- 现代化UI

### 后端技术（完整版）
- 纯PHP实现
- JSON文件存储
- RESTful API设计
- 无数据库依赖

## 扩展开发

### 添加数据库支持
1. 修改 `config.php` 添加数据库配置
2. 创建数据库表：
```sql
CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE UNIQUE,
    work_content TEXT,
    next_plan TEXT,
    issues TEXT,
    suggestions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```
3. 修改 `api.php` 中的数据操作函数

### 添加用户系统
1. 创建用户表
2. 添加登录验证
3. 在报告数据中关联用户ID

### 添加更多导出格式
- Excel (.xlsx) - 可集成PhpSpreadsheet
- PDF - 可使用TCPDF或mPDF
- Word (.docx) - 可使用PHPWord

## 使用说明

### 日历功能
- 日历左侧显示ISO标准周数（今年第几周）
- 显示当月所有涉及的周数
- 点击日期可快速选择并自动计算所属周

### 周报表单
- 选择日期后自动显示周范围信息
- 格式：第XX周 YYYY/MM/DD-YYYY/MM/DD
- 例如：第33周 2025/08/18-2025/08/24
- 支持手动修改日期，自动更新周信息
