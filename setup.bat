@echo off
chcp 65001 >nul
setlocal EnableDelayedExpansion
title AutoShop - Cai dat tren Laragon
cd /d "%~dp0"

echo ==========================================================
echo   AutoShop - Cai dat tu dong cho Laragon
echo   (Hay bam "Start All" trong Laragon truoc khi chay file nay)
echo ==========================================================
echo.

REM ---------- 1. Tim PHP, Composer, MySQL cua Laragon ----------
set "PHP=php"
where php >nul 2>nul
if errorlevel 1 (
    for /d %%D in ("E:\laragon\bin\php\php-*" "C:\laragon\bin\php\php-*" "D:\laragon\bin\php\php-*") do (
        if exist "%%D\php.exe" set "PHP=%%D\php.exe"
    )
)
set "MYSQL=mysql"
where mysql >nul 2>nul
if errorlevel 1 (
    for /d %%D in ("E:\laragon\bin\mysql\mysql-*" "C:\laragon\bin\mysql\mysql-*" "D:\laragon\bin\mysql\mysql-*") do (
        if exist "%%D\bin\mysql.exe" set "MYSQL=%%D\bin\mysql.exe"
    )
)
set "COMPOSER=composer"
where composer >nul 2>nul
if errorlevel 1 (
    if exist "E:\laragon\bin\composer\composer.phar" set "COMPOSER=!PHP! E:\laragon\bin\composer\composer.phar"
    if exist "C:\laragon\bin\composer\composer.phar" set "COMPOSER=!PHP! C:\laragon\bin\composer\composer.phar"
    if exist "D:\laragon\bin\composer\composer.phar" set "COMPOSER=!PHP! D:\laragon\bin\composer\composer.phar"
)
echo PHP:   !PHP!
"!PHP!" -v | findstr /b "PHP"
echo MySQL: !MYSQL!
echo.

for %%F in ("!PHP!") do set "PHP_DIR=%%~dpF"
for %%F in ("!MYSQL!") do set "MYSQL_DIR=%%~dpF"
if defined PHP_DIR set "PATH=!PHP_DIR!;!PATH!"
if defined MYSQL_DIR set "PATH=!MYSQL_DIR!;!PATH!"

REM ---------- 2. Thu vien PHP (vendor) ----------
if not exist "vendor\autoload.php" (
    echo [1/6] Cai thu vien bang Composer...
    call !COMPOSER! install --prefer-dist --no-interaction
    if not exist "vendor\autoload.php" (
        if exist "..\Buoi10\vendor\autoload.php" (
            echo      Composer loi/khong co mang - sao chep vendor tu ..\Buoi10 cung phien ban Laravel 13...
            robocopy "..\Buoi10\vendor" "vendor" /E /NFL /NDL /NJH /NJS /NP >nul
            call !COMPOSER! dump-autoload
        )
    )
) else (
    echo [1/6] Da co thu muc vendor - bo qua.
)
if not exist "vendor\autoload.php" (
    echo [LOI] Chua cai duoc thu vien. Mo Terminal cua Laragon va chay: composer install
    pause & exit /b 1
)

REM ---------- 3. File moi truong .env ----------
if not exist ".env" (
    echo [2/6] Tao file .env tu .env.example...
    copy /y ".env.example" ".env" >nul
    "!PHP!" artisan key:generate --force
) else (
    echo [2/6] Da co file .env - giu nguyen.
)

REM ---------- 4. Tao database ----------
echo [3/6] Tao database "autoshop" (utf8mb4)...
"!MYSQL!" -u root -e "CREATE DATABASE IF NOT EXISTS autoshop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if errorlevel 1 (
    echo [LOI] Khong ket noi duoc MySQL. Hay bam "Start All" trong Laragon roi chay lai.
    pause & exit /b 1
)

REM ---------- 5. Bang + du lieu mau ----------
echo [4/6] Tao bang va du lieu mau (migrate:fresh --seed)...
"!PHP!" artisan migrate:fresh --seed --force
if errorlevel 1 ( pause & exit /b 1 )

echo [5/6] Lien ket thu muc anh (storage:link)...
if exist "public\storage" (
    rmdir "public\storage" 2>nul
    del /f /q "public\storage" 2>nul
)
"!PHP!" artisan storage:link 2>nul

echo [6/6] Xoa cache...
"!PHP!" artisan optimize:clear >nul

echo.
echo ==========================================================
echo   HOAN TAT!
echo   - Mo trinh duyet: http://localhost:8080/AutoCar/AutoCar/public
echo     (Neu chua vao duoc: Laragon ^> Menu ^> Apache ^> Reload, hoac bam Stop roi Start All)
echo   - Hoac:           http://localhost:8080/AutoCar/AutoCar/public
echo   - Admin: admin@autoshop.test / password
echo   - Khach: khach@autoshop.test / password
echo ==========================================================
pause
