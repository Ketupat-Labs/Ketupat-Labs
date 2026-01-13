#!/bin/bash
# Quick start script for Docker setup

echo "CompuPlay Docker Quick Start"
echo "============================"

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "[ERROR] Docker is not running. Please start Docker Desktop."
    exit 1
fi

echo "[OK] Docker is running"

# Determine compose command
if docker compose version > /dev/null 2>&1; then
    DOCKER_COMPOSE="docker compose"
elif docker-compose version > /dev/null 2>&1; then
    DOCKER_COMPOSE="docker-compose"
else
    echo "[ERROR] Neither 'docker compose' nor 'docker-compose' found."
    exit 1
fi

echo "[OK] Using command: $DOCKER_COMPOSE"

# Check if .env exists
if [ ! -f .env ]; then
    echo "Creating .env file from .env.docker..."
    cp .env.docker .env
    echo "[OK] .env file created"
else
    echo "[OK] .env file already exists"
fi

# Build containers
echo ""
echo "Building Docker containers..."
$DOCKER_COMPOSE build

if [ $? -ne 0 ]; then
    echo "[ERROR] Build failed. Please check the errors above."
    exit 1
fi

echo "[OK] Build completed successfully"

# Start containers
echo ""
echo "Starting containers..."
$DOCKER_COMPOSE up -d

if [ $? -ne 0 ]; then
    echo "[ERROR] Failed to start containers. Please check the errors above."
    exit 1
fi

echo "[OK] Containers started successfully"

# Wait for services to be ready
echo ""
echo "Waiting for services to be ready..."
sleep 10

# Check container status
echo ""
echo "Container Status:"
$DOCKER_COMPOSE ps

echo ""
echo "[OK] Setup complete!"
echo ""
echo "Application URLs:"
echo "   - Web Application: http://localhost"
echo "   - WebSocket Server: http://localhost:8080"
echo ""
echo "Useful commands:"
echo "   - View logs: $DOCKER_COMPOSE logs -f"
echo "   - Stop containers: $DOCKER_COMPOSE down"
echo "   - Restart: $DOCKER_COMPOSE restart"
echo ""
echo "For more information, see DOCKER.md"
