#!/bin/sh
set -e

echo "===================================================="
echo "     White-Mart Inventory System Startup"
echo "===================================================="
echo ""

# Check if Docker is running
if ! docker info >/dev/null 2>&1; then
    echo "[ERROR] Docker is not running or not installed."
    echo "Please install Docker Desktop and ensure it is running before starting White-Mart."
    exit 1
fi

echo "[INFO] Docker is running. Starting White-Mart services..."
docker compose up -d

echo ""
echo "[INFO] Waiting for services to initialize..."
echo "[INFO] This might take a minute on the first run."
sleep 10

echo ""
echo "[INFO] Opening White-Mart in your default web browser..."
if command -v xdg-open >/dev/null 2>&1; then
    xdg-open "http://localhost" &
elif command -v open >/dev/null 2>&1; then
    open "http://localhost" &
else
    echo "[INFO] Please navigate to http://localhost in your browser."
fi

echo ""
echo "===================================================="
echo "     White-Mart is now running in the background!"
echo "     You can safely close this window."
echo "     To stop the system, open Docker Desktop and "
echo "     stop the 'white-mart' environment."
echo "===================================================="
