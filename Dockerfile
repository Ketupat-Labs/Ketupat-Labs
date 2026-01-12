FROM php:8.2-cli

# Install system libraries
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libzip-dev zip unzip git curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy composer files first for caching
COPY composer.json composer.lock ./
RUN composer install --optimize-autoloader --no-interaction --no-scripts

# Copy rest of app
COPY . .

# Expose port
EXPOSE 8080

# Runtime: convert PORT to int and run artisan serve
CMD php -r '$_ENV["PORT"] = isset($_ENV["PORT"]) ? (int)$_ENV["PORT"] : 8080;' && \
    php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
