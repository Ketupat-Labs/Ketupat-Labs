#!/bin/bash
set -e

echo "Starting CompuPlay Laravel Application on Render..."

# Debug: Check broadcasting environment variables
echo "=== Environment Check ==="
echo "BROADCAST_CONNECTION: ${BROADCAST_CONNECTION:-NOT SET}"
echo "PUSHER_APP_ID: ${PUSHER_APP_ID:-NOT SET}"
echo "PUSHER_APP_KEY: ${PUSHER_APP_KEY:0:10}... (truncated)"
echo "========================="

# Wait for MySQL to be ready (max 30 seconds)
echo "Waiting for MySQL (max 30s)..."
COUNTER=0
until php -r "new PDO('mysql:host='.\$_ENV['DB_HOST'].';port='.\$_ENV['DB_PORT'].';dbname='.\$_ENV['DB_DATABASE'], \$_ENV['DB_USERNAME'], \$_ENV['DB_PASSWORD']);" 2>/dev/null || [ $COUNTER -eq 15 ]; do
    echo "MySQL is unavailable - sleeping"
    sleep 2
    let COUNTER=COUNTER+1
done

echo "MySQL is up - executing commands"

# Generate application key if not set
if [ -z "$APP_KEY" ]; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Clear and cache configuration
echo "Optimizing application..."

# Ensure all Laravel framework directories exist
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p bootstrap/cache

php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

# Cache configuration for production
if [ "$APP_ENV" = "production" ]; then
    echo "Caching configuration for production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Create storage link
echo "Creating storage link..."
php artisan storage:link || true

# Set proper permissions
echo "Setting permissions..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Set Nginx port from environment variable (default 10000)
PORT=${PORT:-10000}
echo "Configuring Nginx to listen on port $PORT..."
sed -i "s/listen 10000;/listen $PORT;/g" /etc/nginx/http.d/default.conf

echo "Application ready!"

# Execute the main command (Supervisor)
exec "$@"
