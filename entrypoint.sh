#!/bin/bash
set -e

cd /var/www/html

echo "⏳ Waiting for database..."
until php -r "
new PDO(
  'pgsql:host=${DB_HOST};port=${DB_PORT:-5432};dbname=${DB_DATABASE}',
  '${DB_USERNAME}',
  '${DB_PASSWORD}'
);
" 2>/dev/null; do
  echo "  DB not ready, retrying in 2s..."
  sleep 2
done
echo "✅ Database ready"

echo "📁 Creating storage directories..."
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

echo "🗄️  Running migrations..."
php artisan migrate --force

echo "⚙️  Caching config, routes, views..."
php artisan view:cache
php artisan config:cache
php artisan route:cache


echo "✅ App ready, starting php-fpm..."
exec "$@"