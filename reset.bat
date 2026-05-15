@echo off
setlocal enabledelayedexpansion

echo ====================================================
echo      WARNING: White-Mart Inventory System Reset
echo ====================================================
echo.
echo This will permanently DELETE all database records,
echo uploaded files, and system settings!
echo.
echo Type YES and press Enter to confirm, or close this window to cancel.
set /p confirm=Type YES to continue: 

if /I not "%confirm%"=="YES" (
    echo Reset cancelled.
    pause >nul
    exit /b 0
)

echo.
echo [INFO] Checking Docker status...
where docker >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Docker is not installed or not in PATH.
    pause >nul
    exit /b 1
)

docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Docker engine is not running. Please start Docker first.
    pause >nul
    exit /b 1
)

echo.
echo [INFO] Stopping containers and removing data volumes...
docker compose down -v --remove-orphans

echo [INFO] Removing initialization lock file...
if exist "storage\app\.init_complete" del /F /Q "storage\app\.init_complete"
if exist "storage\app\.app-key" del /F /Q "storage\app\.app-key"

echo.
echo ====================================================
echo      SYSTEM RESET COMPLETE
echo ====================================================
echo.
echo All data has been wiped. You can now run start.bat
echo to perform a fresh, clean installation.
echo.
echo Press any key to exit...
pause >nul
