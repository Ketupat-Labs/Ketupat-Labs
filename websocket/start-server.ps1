# PowerShell script to start WebSocket server
Write-Host "Starting WebSocket Server..." -ForegroundColor Green
Write-Host ""
Write-Host "Server will run on ws://localhost:8080" -ForegroundColor Cyan
Write-Host "Press Ctrl+C to stop the server" -ForegroundColor Yellow
Write-Host ""

# Change to project root directory
$scriptPath = Split-Path -Parent $MyInvocation.MyCommand.Path
$projectRoot = Split-Path -Parent $scriptPath
Set-Location $projectRoot

# Start the server
php websocket/server.php

