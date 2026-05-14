# White-Mart Inventory Management System

Welcome to the White-Mart Inventory Management System. This software provides a complete suite to manage stock, daily sales via Excel imports, low-stock alerts, and integrated barcode scanning, all running locally without the need for an active cloud subscription.

This system is designed to run completely **offline** on any modern computer (Windows, macOS, or Linux) using **Docker Desktop**.

---

## 🛠 Requirements

To run this application on your computer, you only need one piece of software:
- **Docker Desktop** (A tool that packages everything the app needs to run automatically)

### How to Install Docker Desktop:
1. Go to [https://www.docker.com/products/docker-desktop](https://www.docker.com/products/docker-desktop)
2. Download the version for your computer (Windows, Mac, or Linux).
3. Run the installer and follow the simple on-screen instructions.
4. Once installed, launch "Docker Desktop" and ensure it is running in the background.

---

## 🚀 Quick Startup Guide

We've made starting the application as simple as clicking a button. 

### For Windows Users:
1. Open the folder where you placed the White-Mart files.
2. Double-click the file named **`start.bat`**.
3. A terminal window will open, start the services in the background, and automatically open your web browser to the system.

### For Mac / Linux Users:
1. Open the folder where you placed the White-Mart files.
2. Double-click the file named **`start.sh`** (or run `./start.sh` in your terminal).
3. The services will start in the background and open the system in your default browser.

> **Note on First Startup**: The very first time you start the system, it may take 1-2 minutes to automatically download the core components, create your database, and seed the default accounts. Please be patient. Subsequent starts will be almost instant.

---

## 🔑 Default Login Credentials

Upon the very first launch, a default Administrator (Sudo) account is created for you:

- **Email**: `whitemart@gmail.com`
- **Password**: `whitemart@gmail.com`

> **IMPORTANT**: Once you log in for the first time, navigate to the User Management section and change this password immediately to secure your system.

---

## 📊 Core Workflows Explained

### 1. The Offline Excel Sales Workflow
To make end-of-day sales recording fast and resilient to internet outages, White-Mart uses a seamless Excel workflow:
1. Navigate to the **Daily Sales Export** page and download the template for the day.
2. The cashier can fill out the sales throughout the day offline using Excel. The sheet has a dropdown of all available products.
3. At the end of the shift, upload the completed Excel sheet on the **Sales Imports** page.
4. The system will automatically validate the rows, deduct the appropriate stock, and log the daily revenue.

### 2. Barcode & SKU Lookup
The system supports barcode scanners. When adding a new product:
1. Click the "Scan Barcode" button or focus on the Barcode field.
2. Scan the physical item.
3. If the item is unknown, the system will automatically query global databases (like OpenFoodFacts and UPCItemDB) over the internet to fetch the product name and category, saving you manual typing. If offline, you can manually enter the product details.

---

## 💾 Backups & Data Safety

Your database and uploaded files (such as sales receipts and spreadsheets) are safely stored in persistent "Docker Volumes". This means even if you stop or restart your computer, your data is completely safe.

### How to Create a Backup:
1. Inside the app, navigate to the **Maintenance & Backups** page in the left sidebar.
2. Click **Create Backup**.
3. The system will bundle your entire database and settings into a single downloadable file. 
4. Keep this file safe (e.g., on a USB drive or Google Drive) to protect against computer hardware failure.

---

## 🔄 Update Workflow

If you receive a new version of the White-Mart software from your development team:
1. Download and extract the new files over your existing folder (replacing the old files).
2. Open **Docker Desktop** and restart the "white-mart" environment.
3. The system will automatically detect the updates, apply any new database changes, and start the app safely without losing any data.

---

## 🩺 Troubleshooting

**Problem: The browser says "Site cannot be reached"**
- Ensure Docker Desktop is open and running in the background.
- Ensure you ran `start.bat` (Windows) or `start.sh` (Mac) and didn't close it prematurely.

**Problem: Missing Database or "Connection Refused"**
- This usually means Docker hasn't fully started the database service yet. Wait 30 seconds and refresh your browser.

**Problem: Forgotten Password**
- If you lose access to the Administrator account, ask your developer to run the `users:bootstrap-sudo` recovery command locally to reset your credentials.

---

*Engineered for performance, reliability, and ease of use. Copyright © White-Mart.*
