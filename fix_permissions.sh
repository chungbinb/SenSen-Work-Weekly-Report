#!/bin/bash
echo "工作周报表系统 - 权限修复脚本"
echo "================================"

# 确保可以创建reports.json文件
if [ ! -f "reports.json" ]; then
    echo "{}" > "reports.json"
    echo "创建了空的reports.json文件"
fi

# 设置权限
chmod 666 reports.json 2>/dev/null || echo "设置reports.json权限失败"
chmod 644 *.php 2>/dev/null || echo "设置PHP文件权限失败"

echo "权限修复完成！"
ls -la *.json *.php | head -5
