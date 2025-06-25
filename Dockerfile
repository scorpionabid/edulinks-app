# EduLinks PHP Application Dockerfile
FROM php:8.1-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apk add --no-cache \
    bash \
    curl \
    freetype-dev \
    g++ \
    gcc \
    git \
    icu-dev \
    jpeg-dev \
    libc-dev \
    libpng-dev \
    libzip-dev \
    make \
    oniguruma-dev \
    postgresql-dev \
    shadow \
    unzip \
    zip

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pdo \
        pdo_pgsql \
        zip

# No need for Composer as this is a custom PHP framework
# COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create application directories
RUN mkdir -p \
    /var/www/html/storage/logs \
    /var/www/html/storage/cache \
    /var/www/html/storage/sessions \
    /var/www/html/public/uploads

# Create www-data user home directory
RUN mkdir -p /var/www && chown www-data:www-data /var/www

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/public/uploads

# Copy application files
COPY . /var/www/html/

# Copy PHP configuration
COPY docker/php/php.ini /usr/local/etc/php/php.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# No PHP dependencies to install via Composer
# This is a custom PHP framework without composer.json

# Switch to www-data user
USER www-data

# Expose port 9000
EXPOSE 9000

# Health check
HEALTHCHECK --interval=30s --timeout=10s --retries=3 \
    CMD php-fpm -t || exit 1

# Start PHP-FPM
CMD ["php-fpm"]