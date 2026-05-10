#!/bin/sh
set -e

echo "==> Waiting for database..."
until php artisan db:monitor --max=1 2>/dev/null; do
  sleep 2
done

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Seeding default data (statuses, etc.)..."
php artisan db:seed --class=StatusSeeder --force

echo "==> Linking storage..."
php artisan storage:link --force 2>/dev/null || true

echo "==> Caching config & routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "==> Optimizing..."
php artisan optimize

echo "==> Starting PHP-FPM..."
exec php-fpm
