#!/bin/bash

set -e

# Change to the application directory
cd /var/www/html

# Check for Composer autoloader instead of only the vendor directory.
# A bind mount can contain an empty/incomplete vendor folder.
if [ ! -f "vendor/autoload.php" ]; then
    echo "Running composer install..."
    composer install
else
    echo "vendor/autoload.php exists. Skipping composer install."
fi

if [ ! -f .env ]; then
    cp .env.example .env
fi

php artisan key:generate --force

# If not, generate it. This is crucial for Laravel's security features.
if grep -qE '^APP_KEY=\s*$' .env || ! grep -q '^APP_KEY=' .env; then
    echo "Generating application key..."
    php artisan key:generate
else
    echo "Application key already exists."
fi

# php artisan key:generate

# Check if node_modules directory exists and run npm install if not
if [ ! -d "node_modules" ]; then
    echo "Running npm install..."
    npm install
else
    echo "node_modules directory already exists. Skipping npm install."
fi
php artisan migrate --force

# Run npm build if node_modules exists, to compile assets.
if [ -d "node_modules" ]; then
    echo "Running npm run build..."
    npm run build
else
    echo "node_modules not found, skipping npm run build."
fi

chown -R $USER:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
# Execute the main command passed to the script (e.g., "php-fpm").
exec "$@"