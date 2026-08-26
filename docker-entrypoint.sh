#!/bin/bash
set -e

# Configure Apache port if PORT env variable is provided (Render sets $PORT dynamically, e.g., 10000)
PORT="${PORT:-80}"
sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:$PORT>/g" /etc/apache2/sites-available/000-default.conf

# Storage symlink
php artisan storage:link || true

# Cache configurations and routes
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Run database migrations if DB is configured
if [ -n "$DB_HOST" ] || [ "$DB_CONNECTION" = "sqlite" ]; then
    echo "Running database migrations..."
    php artisan migrate --force || true
fi

echo "Starting Apache on port $PORT..."
exec apache2-foreground
