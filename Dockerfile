FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    libpq-dev \
    libonig-dev \
    build-essential \
    ffmpeg \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd zip pdo pdo_mysql pgsql pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

RUN printf '%s\n' \
    'post_max_size=128M' \
    'upload_max_filesize=128M' \
    > /usr/local/etc/php/conf.d/99-upload-limits.ini

WORKDIR /var/www/html

# Copy composer files first so Docker caches this layer
# and only re-runs composer install when composer.json/lock changes
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy full app (your .dockerignore keeps node_modules, .env, .git out)
COPY . .

# Finish composer with full app present (runs post-install scripts)
RUN composer dump-autoload --optimize --no-dev

# Build frontend assets inside the image
RUN npm ci && npm run build && rm -rf node_modules

# Fix permissions — only on specific dirs, not entire /var/www/html
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
EXPOSE 9000
CMD ["php-fpm"]