#!/bin/bash
# ML Server Installation Script for Windows/Mac/Linux
# Run this to install everything

echo "=================================================="
echo "🚀 Legazpi ML Server - Installation Script"
echo "=================================================="
echo ""

# Check if Node.js is installed
if ! command -v node &> /dev/null; then
    echo "❌ Node.js is not installed!"
    echo "   Please install from: https://nodejs.org/"
    exit 1
fi

echo "✅ Node.js version: $(node --version)"
echo "✅ npm version: $(npm --version)"
echo ""

# Check if in correct directory
if [ ! -f "package.json" ]; then
    echo "❌ Error: package.json not found!"
    echo "   Please run this script from the backend/ml directory"
    echo "   Usage: cd backend/ml && bash install.sh"
    exit 1
fi

echo "📦 Installing dependencies..."
echo ""

# Clean old installation on Windows
if [[ "$OSTYPE" == "msys" || "$OSTYPE" == "win32" ]]; then
    echo "🪟 Windows detected - cleaning old installation..."
    rm -rf node_modules 2>/dev/null
    rm -f package-lock.json 2>/dev/null
fi

# Install deps
npm install

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Installation successful!"
    echo ""
    echo "=================================================="
    echo "📋 Next Steps:"
    echo "=================================================="
    echo ""
    echo "1. Setup Database:"
    echo "   mysql -u root < schema.sql"
    echo ""
    echo "2. Train Models:"
    echo "   npm run train"
    echo ""
    echo "3. Start Server:"
    echo "   npm start"
    echo ""
    echo "4. Test Health Check:"
    echo "   curl http://localhost:3000/health"
    echo ""
    echo "=================================================="
else
    echo ""
    echo "❌ Installation failed!"
    echo ""
    echo "💡 Troubleshooting Tips:"
    echo "   - Check internet connection"
    echo "   - Try: npm cache clean --force"
    echo "   - Try: npm install --legacy-peer-deps"
    echo "   - On Windows: Use GitBash instead of PowerShell"
    echo "   - See: WINDOWS_SETUP.md for Windows-specific help"
    echo ""
    exit 1
fi
