@echo off
REM ML Server Installation Script for Windows
REM Double-click this file to run automated setup

setlocal enabledelayedexpansion

echo.
echo ==================================================
echo. Legazpi ML Server - Installation Script
echo ==================================================
echo.

REM Check if Node.js is installed
node --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Node.js is not installed!
    echo.
    echo    Please download from: https://nodejs.org/
    echo.
    pause
    exit /b 1
)

echo ✅ Node.js version:
node --version
echo.
echo ✅ npm version:
npm --version
echo.

REM Check if in correct directory
if not exist "package.json" (
    echo ❌ Error: package.json not found!
    echo.
    echo    This script must be run from the backend\ml directory
    echo.
    pause
    exit /b 1
)

echo 📦 Installing dependencies...
echo    (This may take 2-5 minutes)
echo.

REM Clean old installation
echo ✅ Cleaning old installation...
if exist "node_modules" (
    rmdir /s /q node_modules
)
if exist "package-lock.json" (
    del package-lock.json
)

REM Install dependencies
npm install

if errorlevel 1 (
    echo.
    echo ❌ Installation failed!
    echo.
    echo 💡 Troubleshooting:
    echo    - Check your internet connection
    echo    - Try: npm cache clean --force
    echo    - Try: npm install --legacy-peer-deps
    echo    - See: WINDOWS_SETUP.md for more help
    echo.
    pause
    exit /b 1
)

echo.
echo ✅ Installation successful!
echo.
echo ==================================================
echo 📋 Next Steps:
echo ==================================================
echo.
echo 1. Setup Database (open MySQL/Git Bash):
echo    mysql -u root less schema.sql
echo.
echo 2. Train Models:
echo    npm run train
echo.
echo 3. Start Server:
echo    npm start
echo.
echo 4. Test in Browser:
echo    http://localhost:3000/health
echo.
echo ==================================================
echo.
pause
