# ============================================
# Stage 1: Build frontend assets (Node.js)
# ============================================
FROM node:22-alpine AS node-builder
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# ============================================
# Stage 2: Install PHP dependencies
# ============================================
FROM composer:latest AS composer-builder
WORKDIR /app
COPY composer.json composer.lock ./
# --no-dev & --optimize agar vendor super ringan
RUN composer install --no-dev --optimize-autoloader --no-scripts --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --optimize

# ============================================
# Stage 3: Production image (Nginx + PHP-FPM)
# ============================================
FROM php:8.2-fpm-alpine

# Gunakan script ajaib ini untuk install extension PHP dengan bersih
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions pdo_mysql mysqli zip gd intl imap bcmath exif mbstring xml curl opcache pcntl redis

# Install nginx & supervisor
RUN apk add --no-cache nginx supervisor

# PHP production config
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Copy konfigurasi (Pastikan folder docker/ ini ada di repo kamu)
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

WORKDIR /var/www/html

# --- URUTAN PENTING ---
COPY . . 
COPY --from=composer-builder /app/vendor ./vendor
COPY --from=node-builder /app/public/build ./public/build

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Bersihkan sisa-sisa build (Jika tidak pakai .dockerignore)
RUN rm -rf node_modules tests .git docker

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]