#!/bin/bash

echo "🚀 CompuPlay - Automated Setup Script"
echo "======================================"
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file from .env.example..."
    cp .env.example .env
    echo "✅ .env file created"
    echo ""
    echo "⚠️  IMPORTANT: Edit .env file and set your database password"
    echo "   DB_PASSWORD=your_password_here"
    echo ""
    read -p "Press Enter after you've updated .env file..."
fi

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-interaction

# Generate app key
echo "🔑 Generating application key..."
php artisan key:generate

# Setup database
echo "🗄️  Setting up database..."
php artisan db:setup

# Install NPM dependencies
echo "📦 Installing NPM dependencies..."
npm install

# Build assets
echo "🏗️  Building assets..."
npm run build

echo ""
echo "🎉 Setup complete!"
echo ""
echo "To start the development server:"
echo "  php artisan serve"
echo ""
echo "Then visit: http://127.0.0.1:8000"
echo ""
