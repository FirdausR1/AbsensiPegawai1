#!/bin/bash
set -e

# Configure Apache port if PORT env variable is provided (Render sets $PORT dynamically, e.g., 10000)
PORT="${PORT:-80}"
sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:$PORT>/g" /etc/apache2/sites-available/000-default.conf

# Storage symlink
php artisan storage:link || true

# If SQLite is used, ensure database directory and file exist
if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DB_HOST" ]; then
    DB_FILE="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
    mkdir -p "$(dirname "$DB_FILE")"
    if [ ! -f "$DB_FILE" ]; then
        echo "Creating SQLite database at $DB_FILE..."
        touch "$DB_FILE"
    fi
fi

# Ensure storage subdirectories exist
mkdir -p /var/www/html/storage/framework/{sessions,views,cache/data} /var/www/html/storage/logs /var/www/html/storage/app/public/signatures

# Cache configurations, routes, and views
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Run database migrations and seeders
if [ "$DB_CONNECTION" = "sqlite" ] || [ -n "$DB_HOST" ] || [ -z "$DB_CONNECTION" ]; then
    echo "Running database migrations..."
    php artisan migrate --force || true
    echo "Running database seeders..."
    php artisan db:seed --force || true
fi

# Set proper ownership and permissions so www-data can write SQLite and storage files
echo "Setting runtime permissions for www-data..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database || true
if [ -f "/var/www/html/database/database.sqlite" ]; then
    chmod 666 /var/www/html/database/database.sqlite || true
fi

echo "Starting Apache on port $PORT..."
exec apache2-foreground
