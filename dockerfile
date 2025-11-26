# 1. Base PHP image
FROM php:8.2-fpm

# 2. Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    zip \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    nodejs \
    npm \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd

# 3. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Set working directory
WORKDIR /var/www/html

# 5. Copy Laravel files
COPY . /var/www/html

# 6. Set permissions for Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 7. Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# 8. Install Node dependencies & build Vite assets
RUN npm install
RUN npm run build

# 9. Expose port for PHP-FPM
EXPOSE 9000

# 10. Start PHP-FPM
CMD ["php-fpm"]
