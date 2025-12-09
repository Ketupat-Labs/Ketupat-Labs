# Chatbot Module - Quick Setup Script (Windows)
# Run this with: .\setup.ps1

Write-Host "🤖 Ketupats Labs - Chatbot Module Setup" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if Node.js is installed
try {
    $nodeVersion = node --version
    Write-Host "✓ Node.js detected: $nodeVersion" -ForegroundColor Green
} catch {
    Write-Host "❌ Node.js is not installed!" -ForegroundColor Red
    Write-Host "   Download from: https://nodejs.org/" -ForegroundColor Yellow
    exit 1
}

# Check if npm is installed
try {
    $npmVersion = npm --version
    Write-Host "✓ npm detected: $npmVersion" -ForegroundColor Green
} catch {
    Write-Host "❌ npm is not installed!" -ForegroundColor Red
    exit 1
}

Write-Host ""

# Backend setup
Write-Host "📦 Setting up backend..." -ForegroundColor Cyan
Set-Location backend

if (-not (Test-Path ".env")) {
    Write-Host "Creating .env file..." -ForegroundColor Yellow
    
    $envContent = @"
PORT=3000
NODE_ENV=development
GEMINI_API_KEY=your_api_key_here
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ketupats_labs
DB_USERNAME=root
DB_PASSWORD=
SESSION_SECRET=$(Get-Random)_$(Get-Date -Format 'yyyyMMddHHmmss')
ALLOWED_ORIGINS=http://localhost:8000,http://127.0.0.1:8000
LOG_LEVEL=debug
"@
    
    $envContent | Out-File -FilePath ".env" -Encoding UTF8
    Write-Host "✓ .env file created" -ForegroundColor Green
    Write-Host "⚠️  Remember to add your GEMINI_API_KEY to backend\.env" -ForegroundColor Yellow
} else {
    Write-Host "✓ .env file already exists" -ForegroundColor Green
}

Write-Host "Installing backend dependencies..." -ForegroundColor Yellow
npm install

if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Backend dependencies installed" -ForegroundColor Green
} else {
    Write-Host "❌ Failed to install backend dependencies" -ForegroundColor Red
    Set-Location ..
    exit 1
}

Set-Location ..
Write-Host ""

# Frontend check
Write-Host "📦 Frontend files ready" -ForegroundColor Green
Write-Host "   Open frontend\index.html in browser to test" -ForegroundColor Gray
Write-Host ""

# Summary
Write-Host "✅ Setup Complete!" -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Cyan
Write-Host "1. Edit backend\.env and add your GEMINI_API_KEY" -ForegroundColor White
Write-Host "2. Start backend server: cd backend ; npm start" -ForegroundColor White
Write-Host "3. Open frontend\index.html in browser" -ForegroundColor White
Write-Host ""
Write-Host "For Laravel integration:" -ForegroundColor Cyan
Write-Host "1. Run migrations: php artisan migrate" -ForegroundColor White
Write-Host "2. Start Laravel server: php artisan serve" -ForegroundColor White
Write-Host ""
Write-Host "📚 Read README.md for more information" -ForegroundColor Yellow
Write-Host "🚀 Happy coding!" -ForegroundColor Green
