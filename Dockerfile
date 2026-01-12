FROM php:8.2-cli

# Install system libraries
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files first for caching
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --optimize-autoloader --no-interaction --no-scripts

# Copy rest of app
COPY . .

# Clear config cache to make sure env vars are loaded
RUN php artisan config:clear

# Expose port (Railway usually sets this dynamically)
EXPOSE 8080

# Fix: make PORT an integer and run artisan serve
CMD php -r '$_ENV["PORT"] = isset($_ENV["PORT"]) ? (int)$_ENV["PORT"] : 8080;' && \
    php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
