# CompuPlay Docker Quick Start Script for Windows
# Run this script in PowerShell

Write-Host "CompuPlay Docker Quick Start" -ForegroundColor Cyan
Write-Host "============================" -ForegroundColor Cyan

# Check if Docker is running
try {
    docker info | Out-Null
    Write-Host "[OK] Docker is running" -ForegroundColor Green
} catch {
    Write-Host "[ERROR] Docker is not running or not installed. Please start Docker Desktop." -ForegroundColor Red
    exit 1
}

# Determine correct docker compose command
$dockerComposeCmd = "docker compose"
try {
    docker compose version | Out-Null
} catch {
    # If 'docker compose' fails, try 'docker-compose'
    try {
        docker-compose version | Out-Null
        $dockerComposeCmd = "docker-compose"
    } catch {
        Write-Host "[ERROR] Neither 'docker compose' nor 'docker-compose' found." -ForegroundColor Red
        exit 1
    }
}
Write-Host "[OK] Using command: $dockerComposeCmd" -ForegroundColor Green

# Check if .env exists
if (-not (Test-Path .env)) {
    Write-Host "Creating .env file from .env.docker..." -ForegroundColor Yellow
    Copy-Item .env.docker .env
    Write-Host "[OK] .env file created" -ForegroundColor Green
} else {
    Write-Host "[OK] .env file already exists" -ForegroundColor Green
}

# Build containers
Write-Host ""
Write-Host "Building Docker containers..." -ForegroundColor Yellow
Invoke-Expression "$dockerComposeCmd build"

if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] Build failed. Please check the errors above." -ForegroundColor Red
    exit 1
}

Write-Host "[OK] Build completed successfully" -ForegroundColor Green

# Start containers
Write-Host ""
Write-Host "Starting containers..." -ForegroundColor Yellow
Invoke-Expression "$dockerComposeCmd up -d"

if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] Failed to start containers. Please check the errors above." -ForegroundColor Red
    exit 1
}

Write-Host "[OK] Containers started successfully" -ForegroundColor Green

# Wait for services to be ready
Write-Host ""
Write-Host "Waiting for services to be ready..." -ForegroundColor Yellow
Start-Sleep -Seconds 10

# Check container status
Write-Host ""
Write-Host "Container Status:" -ForegroundColor Cyan
Invoke-Expression "$dockerComposeCmd ps"

Write-Host ""
Write-Host "Setup complete!" -ForegroundColor Green
Write-Host ""
Write-Host "Application URLs:" -ForegroundColor Cyan
Write-Host "   - Web Application: http://localhost" -ForegroundColor White
Write-Host "   - WebSocket Server: http://localhost:8080" -ForegroundColor White
Write-Host ""
Write-Host "Useful commands:" -ForegroundColor Cyan
Write-Host "   - View logs: $dockerComposeCmd logs -f" -ForegroundColor White
Write-Host "   - Stop containers: $dockerComposeCmd down" -ForegroundColor White
Write-Host "   - Restart: $dockerComposeCmd restart" -ForegroundColor White
Write-Host ""
Write-Host "For more information, see DOCKER.md" -ForegroundColor Cyan
