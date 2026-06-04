#!/bin/bash

set -e

# Change to the application directory
cd /var/www/html

# Check for Composer autoloader instead of only the vendor directory.
# A bind mount can contain an empty/incomplete vendor folder.
if [ ! -f "vendor/autoload.php" ]; then
    echo "Running composer install..."
    composer install --no-interaction --no-ansi 2>&1 || true
    # If composer still failed, show vendor status
    if [ ! -f "vendor/autoload.php" ]; then
        echo "Composer install may have failed, checking vendor directory..."
        ls -la vendor/ 2>/dev/null | head -20 || echo "vendor directory is empty"
    fi
else
    echo "vendor/autoload.php exists. Skipping composer install."
fi

if [ ! -f .env ]; then
    echo ".env file not found, copying .env.example..."
    cp .env.example .env
fi

# Generate key only if it doesn't exist to avoid invalidating sessions on every restart
if [ -z "$(grep APP_KEY .env | cut -d'=' -f2)" ]; then
    echo "Generating application key..."
    php artisan key:generate --force
else
    echo "Application key already exists."
fi

# php artisan key:generate

# Check if node_modules directory exists and run npm install if not
# In dev, we might want to always run it or check package.json
echo "Running npm install to ensure all dependencies are present..."
npm install
php artisan migrate --force

# Run npm build if node_modules exists, to compile assets.
if [ -d "node_modules" ]; then
    echo "Running npm run build..."
    npm run build
else
    echo "node_modules not found, skipping npm run build."
fi

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
# Execute the main command passed to the script (e.g., "php-fpm").
exec "$@"
