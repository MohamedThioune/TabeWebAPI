# Use the official PHP image as a base
FROM php:8.3-fpm 

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libpq-dev \
    libzip-dev \
    zip \
    nginx
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    zip

RUN pecl install redis \
    && docker-php-ext-enable redis
    
# Install Composer
COPY --from=composer:2.5 /usr/bin/composer /usr/bin/composer

# Set working directory and copy application files
WORKDIR /var/www/html
COPY . .

# Clear caches and optimize
RUN php artisan config:clear || true
RUN php artisan cache:clear || true
RUN php artisan optimize:clear || true

# ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader

# COPY NGINX CONFIG
COPY docker/nginx/default.conf /etc/nginx/sites-available/default

# COPY STARTING SCRIPT
COPY docker/php/start.sh /start.sh
RUN chmod +x /start.sh

# Set permissions
RUN chown -R www-data:www-data /var
RUN chown -R www-data:www-data /var/www/html
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

CMD ["/start.sh"]
