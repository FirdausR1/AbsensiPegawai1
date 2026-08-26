#!/bin/bash
set -e

# Configure Apache port if PORT env variable is provided (Render sets $PORT dynamically, e.g., 10000)
PORT="${PORT:-80}"
sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:$PORT>/g" /etc/apache2/sites-available/000-default.conf

# Storage symlink
php artisan storage:link || true

# If SQLite is used, ensure database file exists
if [ "$DB_CONNECTION" = "sqlite" ]; then
    DB_FILE="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
    if [ ! -f "$DB_FILE" ]; then
        echo "Creating SQLite database at $DB_FILE..."
        touch "$DB_FILE"
    fi
    chown www-data:www-data "$DB_FILE" || true
    chmod 664 "$DB_FILE" || true
fi

# Cache configurations, routes, and views
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Run database migrations and seeders if DB is configured
if [ "$DB_CONNECTION" = "sqlite" ] || [ -n "$DB_HOST" ]; then
    echo "Running database migrations..."
    php artisan migrate --force || true
    echo "Running database seeders..."
    php artisan db:seed --force || true
fi

echo "Starting Apache on port $PORT..."
exec apache2-foreground
