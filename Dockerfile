# Base image
FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libzip-dev zip unzip git curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files first (caching)
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-interaction --no-scripts

# Copy rest of the application
COPY . .

# Copy entrypoint script
COPY docker-entrypoint.php /usr/local/bin/docker-entrypoint.php
RUN chmod +x /usr/local/bin/docker-entrypoint.php

# Expose port
EXPOSE 8080

# Runtime: entrypoint handles PORT + config + serve
CMD ["php", "/usr/local/bin/docker-entrypoint.php"]
