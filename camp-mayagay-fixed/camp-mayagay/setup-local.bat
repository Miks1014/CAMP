@echo off
cd /d "%~dp0"
title Camp Mayagay

where php >nul 2>nul
if errorlevel 1 goto nophp

php -m | findstr /i pdo_mysql >nul
if errorlevel 1 goto nomysqlext

if not exist .env.local goto makeenv

echo Creating admin/staff accounts if missing...
php php\seed.php
if errorlevel 1 goto nodb

echo.
echo ==============================================
echo  Camp Mayagay is running: http://localhost:8080
echo  Admin login: admin / Admin@123
echo  Close this window to stop the server.
echo ==============================================
start "" http://localhost:8080
php -S localhost:8080 router.php
goto end

:nophp
echo PHP is not installed. Open PowerShell and run:  winget install PHP.PHP.8.3
echo Then close this window and run setup-local.bat again.
goto end

:nomysqlext
echo PHP is installed but the MySQL extension is off.
echo Run:  php --ini   to find php.ini, open it, and remove the ; in front of
echo   extension=pdo_mysql   extension=mbstring   extension=openssl
echo Then run setup-local.bat again.
goto end

:makeenv
copy .env.local.example .env.local >nul
echo Created .env.local - Notepad will open. Put your MySQL root password after DB_PASS=
echo and save, then run setup-local.bat again.
notepad .env.local
goto end

:nodb
echo.
echo Could not reach the database. In MySQL Workbench run database\setup-local.sql
echo once, and check DB_USER / DB_PASS in .env.local.
goto end

:end
pause
