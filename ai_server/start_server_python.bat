@echo off
title Axeron AI Server
echo ==========================================
echo Khoi dong may chu AI Semantic Search Axeron
echo ==========================================
echo.
echo Kiem tra va cai dat thu vien (Neu chua co)...
pip install -r requirements.txt
echo.
echo Dang chay Server AI...
python app.py
pause
