@echo off
echo ========================================
echo   AIMS HRMS - Starting Application
echo ========================================
echo.

REM Check if PHP is installed
php --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: PHP is not installed or not in PATH.
    echo.
    echo Please install PHP from: https://www.php.net/downloads
    echo Or add PHP to your system PATH.
    echo.
    pause
    exit /b 1
)

REM Check if database exists, if not run setup
if not exist "data\hrms.sqlite" (
    echo Database not found. Running setup...
    echo.
    php setup.php
    echo.
    echo Setup complete!
    echo.
)

REM Start PHP built-in server
echo Starting PHP server on http://localhost:8080
echo.
echo Press Ctrl+C to stop the server
echo.
php -S localhost:8080
