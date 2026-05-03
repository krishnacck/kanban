FROM php:8.3-fpm-alpine

# System dependencies
RUN apk add --no-cache \
    bash \
    curl \
    git \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    nodejs \
    npm \
    mysql-client

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        xml \
        opcache

# Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application files
COPY . .

# Install PHP dependencies (no dev in production)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Install Node dependencies and build assets
RUN npm ci --ignore-scripts && npm run build && rm -rf node_modules

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html/storage \
 && chmod -R 755 /var/www/html/bootstrap/cache

# PHP-FPM config: listen on socket
RUN echo "pm.max_children = 20" >> /usr/local/etc/php-fpm.d/www.conf \
 && echo "pm.start_servers = 5" >> /usr/local/etc/php-fpm.d/www.conf \
 && echo "pm.min_spare_servers = 2" >> /usr/local/etc/php-fpm.d/www.conf \
 && echo "pm.max_spare_servers = 10" >> /usr/local/etc/php-fpm.d/www.conf

EXPOSE 9000

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

CMD ["/entrypoint.sh"]
