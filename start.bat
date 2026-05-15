@echo off
setlocal enabledelayedexpansion

echo ====================================================
echo      White-Mart Inventory System Startup
echo ====================================================
echo.

:: Step 1: Check if Docker is installed
where docker >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Docker is not installed or not in PATH.
    echo Please install Docker Desktop from https://www.docker.com/products/docker-desktop/
    echo Press any key to exit...
    pause >nul
    exit /b 1
)

:: Step 2: Check if Docker engine is running
docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo [INFO] Docker engine is not running. Attempting to start Docker Desktop...
    if exist "C:\Program Files\Docker\Docker\Docker Desktop.exe" (
        start "" "C:\Program Files\Docker\Docker\Docker Desktop.exe"
        echo [INFO] Waiting for Docker to start...
        :wait_docker
        timeout /t 5 >nul
        docker info >nul 2>&1
        if !errorlevel! neq 0 (
            goto wait_docker
        )
        echo [INFO] Docker is now running.
    ) else (
        echo [ERROR] Could not find Docker Desktop. Please start it manually.
        echo Press any key to exit...
        pause >nul
        exit /b 1
    )
) else (
    echo [INFO] Docker is running.
)

:: Step 3: Check if required ports are free (80, 3306)
echo [INFO] Checking required ports...
netstat -ano | findstr ":80 " >nul
if %errorlevel% equ 0 (
    echo [WARNING] Port 80 might be in use. White-Mart might not be accessible if it conflicts.
)
netstat -ano | findstr ":3306 " >nul
if %errorlevel% equ 0 (
    echo [WARNING] Port 3306 might be in use. Database binding might fail if it conflicts.
)

:: Step 4: Automatically create .env if missing
if not exist ".env" (
    if exist ".env.example" (
        echo [INFO] Creating .env file from .env.example...
        copy .env.example .env >nul
    )
)

:: Step 5: Build and start containers
echo [INFO] Building and starting White-Mart services (this might take a while on first run)...
docker compose up -d --build

:: Step 6: Wait for MySQL health check
echo [INFO] Waiting for MySQL database to become healthy...
:wait_mysql
docker inspect --format="{{if .State.Health}}{{.State.Health.Status}}{{end}}" white-mart-mysql | findstr "healthy" >nul
if %errorlevel% neq 0 (
    timeout /t 2 >nul
    goto wait_mysql
)
echo [INFO] MySQL database is ready.

:: Step 7 & 8: Run Laravel initialization automatically (and seed sudo user)
echo [INFO] Initializing Laravel application...
docker exec white-mart-app php artisan key:generate --no-interaction
docker exec white-mart-app php artisan migrate --force --no-interaction
docker exec white-mart-app php artisan storage:link --force --no-interaction
docker exec white-mart-app php artisan optimize --no-interaction
docker exec white-mart-app php artisan config:cache --no-interaction
docker exec white-mart-app php artisan route:cache --no-interaction
docker exec white-mart-app php artisan view:cache --no-interaction

echo [INFO] Seeding default administrator account...
docker exec white-mart-app php artisan users:bootstrap-sudo akingtoyo@gmail.com --name="System Admin" --password="akingtoyo@gmail.com" --no-interaction

:: Step 9: Verify nginx is ready
echo [INFO] Verifying Web Server (Nginx)...
:wait_nginx
docker inspect --format="{{if .State.Health}}{{.State.Health.Status}}{{end}}" white-mart-nginx | findstr "healthy" >nul
if %errorlevel% neq 0 (
    timeout /t 2 >nul
    goto wait_nginx
)

:: Step 10: Automatically open browser
echo [INFO] Opening White-Mart in your default web browser...
start http://localhost

:: Step 11: Display success message
echo.
echo ====================================================
echo      White-Mart Inventory System is Ready!
echo.
echo      Default Admin Login:
echo      Email: akingtoyo@gmail.com
echo      Password: akingtoyo@gmail.com
echo.
echo      You can safely close this window.
echo      To stop the system, run stop.bat
echo ====================================================
echo Press any key to exit...
pause >nul
