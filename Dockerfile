FROM php:8.3-fpm

# System dependencies
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

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Node.js (ONLY for build stage)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# PHP config
RUN printf '%s\n' \
    'post_max_size=128M' \
    'upload_max_filesize=128M' \
    > /usr/local/etc/php/conf.d/99-upload-limits.ini

WORKDIR /var/www/html

# Copy app
COPY . /var/www/html

# Install backend dependencies
RUN composer install --no-dev --optimize-autoloader

# Install frontend + build (IMPORTANT: ONLY ONCE HERE)
RUN npm install
RUN npm run build

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Entry script
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]

EXPOSE 9000

CMD ["php-fpm"]