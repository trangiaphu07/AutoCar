@echo off
chcp 65001 >nul
title AutoShop - Scheduler (tu huy don VNPay qua han moi 5 phut)
cd /d "%~dp0"
set "PHP=php"
where php >nul 2>nul
if errorlevel 1 (
    for /d %%D in ("E:\laragon\bin\php\php-*" "C:\laragon\bin\php\php-*" "D:\laragon\bin\php\php-*") do (
        if exist "%%D\php.exe" set "PHP=%%D\php.exe"
    )
)
echo Dang chay scheduler... (dong cua so nay de dung)
"%PHP%" artisan schedule:work
