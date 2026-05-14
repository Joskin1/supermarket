# White-Mart Local Deployment Guide

## 1. Windows Installation Guide
White-Mart is designed to be run locally on a Windows PC without requiring advanced technical knowledge.

**Prerequisites:**
1. Download and install [Docker Desktop for Windows](https://www.docker.com/products/docker-desktop/).
2. During installation, ensure the **WSL 2 backend** is enabled if prompted.
3. Once installed, start Docker Desktop and ensure the engine is running (the icon in your system tray should be green/stable).

**Installation Steps:**
1. Extract the provided White-Mart ZIP file to a folder on your computer (e.g., `C:\WhiteMart`).
2. Double-click the `start.bat` file in the folder.
3. A command prompt window will appear. It will automatically build the necessary images and start the services. This may take up to 5-10 minutes on the first run.
4. Once completed, your default web browser will automatically open to `http://localhost`.
5. Login with the default credentials:
   - **Email:** `admin@white-mart.local`
   - **Password:** `password`

**Stopping and Restarting:**
- To stop the system safely, double-click `stop.bat`.
- To restart the system, double-click `restart.bat`.

## 2. Architecture Explanation
The application operates on an **Offline-First Docker Architecture**:
- **white-mart-app**: The core Laravel 13 PHP-FPM container. It handles all application logic, caching, queues, and background jobs using Supervisor.
- **white-mart-nginx**: The web server container. It serves static assets (Vite builds) directly and proxies PHP requests to the app container.
- **white-mart-mysql**: The database container (MySQL 8.0). Data is persisted using Docker volumes so it survives computer restarts.
- **white-mart-phpmyadmin**: A database management tool available on port 8081 for advanced troubleshooting.

## 3. Troubleshooting Guide
**Issue: "Docker is not running or not installed"**
- Fix: Open Docker Desktop manually from your Start Menu. Wait until it says "Engine Running" before double-clicking `start.bat`.

**Issue: Webpage shows "502 Bad Gateway"**
- Fix: The PHP container might still be starting. Refresh the page after 30 seconds.

**Issue: My database changes were lost!**
- Fix: This should not happen because we use a Docker volume (`mysql_data`). Ensure you do not "Reset to factory defaults" in Docker Desktop, as this deletes all volumes. Always use `stop.bat` to shutdown the system properly.

**Issue: Forgot Admin Password**
- Fix: Double-click `restart.bat`. The startup script automatically re-bootstraps the `admin@white-mart.local` user with the password `password` if it detects issues, or you can run `docker compose exec app php artisan users:bootstrap-sudo admin@white-mart.local --password="newpassword"` from the command prompt.

## 4. Security Recommendations
Although designed for local offline use, basic security must be maintained:
1. **Change Default Credentials**: Immediately change the default admin password after first login.
2. **Local Network Access**: If you want to access White-Mart from a tablet on the same Wi-Fi, ensure your Windows Firewall allows inbound connections on port 80. Do NOT expose port 80 to the public internet without an SSL proxy (like Cloudflare or Nginx reverse proxy).
3. **Database Security**: The MySQL root password in `docker-compose.yml` should be changed if deploying in a less trusted environment.

## 5. Scalability Recommendations
Optimizations in place for a 1000+ product catalog:
- **Opcache Enabled**: Precompiled PHP scripts for fast execution on low-resource laptops.
- **Database Indexing**: Eager loading and compound indexes handle large Excel imports.
- **Memory Optimization**: The queue worker handles barcode/image processing asynchronously to keep the Filament dashboard fast.
- **Supervisor**: Automatically restarts failed queue workers ensuring background jobs never halt.

## 6. Production Readiness Checklist
- [x] Containers build locally (no pull dependencies).
- [x] Restart policies set to `unless-stopped`.
- [x] Storage/Data persistence guaranteed via volumes.
- [x] Automatic database migration/seeding on first run.
- [x] Explicit container health checks.
- [x] Hardcoded fallback Sudo user logic via `.bat` bootstrap.
- [x] App key generation automated and persisted in `.app-key`.
- [x] Supervisor configured for queue workers and scheduler.
