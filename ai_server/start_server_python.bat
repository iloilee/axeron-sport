@echo off
cd /d "%~dp0"
title Axeron AI Server Python
echo ==========================================
echo Khoi dong may chu AI Semantic Search Axeron
echo ==========================================
echo.
echo Kiem tra va cai dat thu vien (Neu chua co)...
pip install -r requirements.txt --no-warn-script-location
echo.
echo Dang chay Server AI Python...
set PYTHONUTF8=1
python -u app.py
