@echo off
setlocal

echo ====================================================
echo      White-Mart Inventory System Startup
echo ====================================================
echo.

:: Check if Docker is installed and running
docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Docker is not running or not installed.
    echo Please install Docker Desktop and ensure it is running before starting White-Mart.
    echo Press any key to exit...
    pause >nul
    exit /b 1
)

echo [INFO] Docker is running. Starting White-Mart services...
docker compose up -d

echo.
echo [INFO] Waiting for services to initialize...
echo [INFO] This might take a minute on the first run.
timeout /t 10 /nobreak >nul

echo.
echo [INFO] Opening White-Mart in your default web browser...
start http://localhost

echo.
echo ====================================================
echo      White-Mart is now running in the background!
echo      You can safely close this window.
echo      To stop the system, open Docker Desktop and 
echo      stop the "white-mart" environment.
echo ====================================================
echo Press any key to exit...
pause >nul
