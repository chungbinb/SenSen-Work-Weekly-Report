#!/bin/bash

# 森森信息部工作周报表系统 - 本地测试启动脚本
# 使用Python内置服务器运行静态文件预览

echo "🚀 启动森森信息部工作周报表系统测试环境..."
echo "📍 当前目录: $(pwd)"
echo ""

# 检查Python是否安装
if command -v python3 &> /dev/null; then
    PYTHON_CMD="python3"
elif command -v python &> /dev/null; then
    PYTHON_CMD="python"
else
    echo "❌ 错误: 未找到Python，请先安装Python"
    exit 1
fi

echo "✅ 使用Python: $PYTHON_CMD"
echo ""

# 设置端口
PORT=8000

echo "🌐 启动Web服务器..."
echo "📄 预览页面: http://localhost:$PORT/pdf_preview.html"
echo "🧪 测试页面: http://localhost:$PORT/test_pdf.html"
echo "📋 主页面: http://localhost:$PORT/index.php (需要PHP服务器)"
echo ""
echo "⚠️  注意: 这只是静态文件预览，PHP功能需要实际的PHP服务器"
echo "💡 可以在预览页面中查看PDF导出的布局效果"
echo ""
echo "按 Ctrl+C 停止服务器"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# 启动Python HTTP服务器
$PYTHON_CMD -m http.server $PORT
