@echo off
setlocal

echo ====================================================
echo      White-Mart Inventory System Shutdown
echo ====================================================
echo.

:: Check if Docker is installed and running
docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Docker is not running or not installed.
    echo Press any key to exit...
    pause >nul
    exit /b 1
)

echo [INFO] Stopping White-Mart services...
docker compose down

echo.
echo ====================================================
echo      White-Mart services have been stopped.
echo ====================================================
echo Press any key to exit...
pause >nul
