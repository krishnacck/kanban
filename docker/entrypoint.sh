#!/bin/sh
set -e

echo "==> Waiting for database..."
until php artisan db:monitor --max=1 2>/dev/null; do
  sleep 2
done

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Caching config & routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Starting PHP-FPM..."
exec php-fpm
