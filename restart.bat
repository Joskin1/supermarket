@echo off
setlocal enabledelayedexpansion

echo ====================================================
echo      White-Mart Inventory System Restart
echo ====================================================
echo.

:: Check if Docker is installed
where docker >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Docker is not installed or not in PATH.
    echo Press any key to exit...
    pause >nul
    exit /b 1
)

:: Check if Docker engine is running
docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo [INFO] Docker is not running.
    echo Please run start.bat to initialize the system properly.
    echo Press any key to exit...
    pause >nul
    exit /b 1
)

echo [INFO] Stopping current White-Mart services...
docker compose down

echo.
echo [INFO] Building and starting White-Mart services...
docker compose up -d --build

echo.
echo [INFO] Waiting for services to initialize...
timeout /t 10 /nobreak >nul

echo.
echo [INFO] Opening White-Mart in your default web browser...
start http://localhost

echo.
echo ====================================================
echo      White-Mart has been restarted!
echo      You can safely close this window.
echo ====================================================
echo Press any key to exit...
pause >nul
