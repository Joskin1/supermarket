@echo off
setlocal enabledelayedexpansion

echo ====================================================
echo      White-Mart Inventory System Shutdown
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
    echo [INFO] Docker is not running, so containers are already stopped.
    echo Press any key to exit...
    pause >nul
    exit /b 0
)

echo [INFO] Stopping White-Mart services safely...
docker compose down

echo.
echo ====================================================
echo      White-Mart services have been stopped.
echo ====================================================
echo Press any key to exit...
pause >nul
