# ============================================
# Stage 1: Build frontend assets (Node.js)
# ============================================
FROM node:20-alpine AS node-builder
WORKDIR /app

# Ambil rahasia dari GitHub Actions
ARG VITE_REVERB_APP_KEY
ARG VITE_REVERB_HOST
ARG VITE_REVERB_PORT
ARG VITE_REVERB_SCHEME

COPY package*.json ./
RUN npm ci

COPY . .

# Masukkan rahasia ke proses build Vite
RUN VITE_REVERB_APP_KEY=${VITE_REVERB_APP_KEY} \
    VITE_REVERB_HOST=${VITE_REVERB_HOST} \
    VITE_REVERB_PORT=${VITE_REVERB_PORT} \
    VITE_REVERB_SCHEME=${VITE_REVERB_SCHEME} \
    npm run build

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
FROM php:8.3-fpm-alpine

# Gunakan script ajaib ini untuk install extension PHP dengan bersih
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions pdo_mysql mysqli zip gd intl imap bcmath exif mbstring xml curl opcache pcntl redis

# Install nginx & supervisor
RUN apk add --no-cache nginx supervisor

# --- Membuat folder log Supervisor ---
RUN mkdir -p /var/log/supervisor /var/log/nginx /var/cache/nginx \
    && chown -R www-data:www-data /var/log/supervisor /var/log/nginx /var/cache/nginx

# PHP production config
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Copy konfigurasi
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

# Set permissions untuk Laravel
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Bersihkan sisa-sisa build
RUN rm -rf node_modules tests .git docker

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]