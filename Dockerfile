FROM ghcr.io/railwayapp/nixpacks:latest

WORKDIR /app

COPY . .

# Install required PHP extensions for PhpOffice
RUN install-php-extensions gd zip

# Install composer dependencies
RUN composer install --optimize-autoloader --no-interaction --no-scripts

# Laravel port
EXPOSE 8080

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
