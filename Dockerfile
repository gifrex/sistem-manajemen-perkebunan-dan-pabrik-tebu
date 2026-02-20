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
RUN composer install --no-dev --optimize-autoloader --no-scripts --ignore-platform-reqs

COPY . .
RUN composer dump-autoload --optimize


# ============================================
# Stage 3: Production image (Nginx + PHP-FPM)
# ============================================
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    libpng-dev \
    libzip-dev \
    icu-dev \
    imap-dev \
    openssl-dev \
    krb5-dev \
    oniguruma-dev \
    libxml2-dev \
    curl-dev

# Install PHP extensions (matching your current setup)
RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install \
    pdo_mysql \
    mysqli \
    zip \
    gd \
    intl \
    imap \
    bcmath \
    exif \
    mbstring \
    xml \
    curl \
    opcache \
    pcntl

# Install Redis extension
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# PHP production config
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Copy config files
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

WORKDIR /var/www/html

# Copy application code
COPY --from=composer-builder /app/vendor ./vendor
COPY --from=node-builder /app/public/build ./public/build
COPY . .

# Remove unnecessary files
RUN rm -rf node_modules tests .git .env.example docker

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Create nginx cache and log dirs
RUN mkdir -p /var/cache/nginx /var/log/nginx /var/log/supervisor

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]