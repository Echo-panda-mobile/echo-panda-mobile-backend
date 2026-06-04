#!/bin/bash

set -e

cd /var/www/html

echo "🚀 Starting Laravel container..."

# Ensure .env exists
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Generate app key ONLY if missing
if ! grep -q "APP_KEY=base64" .env; then
    php artisan key:generate --force || true
fi

# Run migrations safely
php artisan migrate --force || true

# Fix permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "✅ Container ready"

exec "$@"