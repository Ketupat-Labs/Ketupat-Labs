#!/bin/bash
# Chatbot Module - Quick Setup Script
# This script helps you set up the module quickly

echo "🤖 Ketupats Labs - Chatbot Module Setup"
echo "========================================"
echo ""

# Check if Node.js is installed
if ! command -v node &> /dev/null; then
    echo "❌ Node.js is not installed!"
    echo "   Download from: https://nodejs.org/"
    exit 1
fi

echo "✓ Node.js detected: $(node --version)"

# Check if npm is installed
if ! command -v npm &> /dev/null; then
    echo "❌ npm is not installed!"
    exit 1
fi

echo "✓ npm detected: $(npm --version)"
echo ""

# Backend setup
echo "📦 Setting up backend..."
cd backend || exit

if [ ! -f ".env" ]; then
    echo "Creating .env file..."
    cat > .env << EOF
PORT=3000
NODE_ENV=development
GEMINI_API_KEY=your_api_key_here
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ketupats_labs
DB_USERNAME=root
DB_PASSWORD=
SESSION_SECRET=$(openssl rand -base64 32 2>/dev/null || echo "change_this_secret")
ALLOWED_ORIGINS=http://localhost:8000,http://127.0.0.1:8000
LOG_LEVEL=debug
EOF
    echo "✓ .env file created"
    echo "⚠️  Remember to add your GEMINI_API_KEY to backend/.env"
else
    echo "✓ .env file already exists"
fi

echo "Installing backend dependencies..."
npm install

if [ $? -eq 0 ]; then
    echo "✓ Backend dependencies installed"
else
    echo "❌ Failed to install backend dependencies"
    exit 1
fi

cd ..
echo ""

# Frontend check
echo "📦 Frontend files ready"
echo "   Open frontend/index.html in browser to test"
echo ""

# Summary
echo "✅ Setup Complete!"
echo ""
echo "Next steps:"
echo "1. Edit backend/.env and add your GEMINI_API_KEY"
echo "2. Start backend server: cd backend && npm start"
echo "3. Open frontend/index.html in browser"
echo ""
echo "For Laravel integration:"
echo "1. Run migrations: php artisan migrate"
echo "2. Start Laravel server: php artisan serve"
echo ""
echo "📚 Read README.md for more information"
echo "🚀 Happy coding!"
