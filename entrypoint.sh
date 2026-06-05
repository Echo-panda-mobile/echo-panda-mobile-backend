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

echo "🗄️  Running migrations..."
php artisan migrate --force

echo "⚙️  Caching config, routes, views..."
php artisan config:cache
php artisan route:cache


echo "✅ App ready, starting php-fpm..."
exec "$@"