#!/bin/bash

# Default port to 80 if Render doesn't provide one
export PORT=${PORT:-80}

# Render generates a base64 value for APP_KEY. Laravel expects that value to
# be explicitly marked as base64-encoded.
if [[ -n "${APP_KEY:-}" && "${APP_KEY}" != base64:* ]]; then
    export APP_KEY="base64:${APP_KEY}"
fi

if [[ -n "${APP_URL:-}" && "${APP_URL}" != http://* && "${APP_URL}" != https://* ]]; then
    export APP_URL="https://${APP_URL}"
fi

# Configure Apache to listen on the correct PORT dynamically at runtime
echo "Listen ${PORT}" > /etc/apache2/ports.conf
sed -i "s/:80/:${PORT}/g" /etc/apache2/sites-available/000-default.conf

echo "Running migrations and caching..."
php artisan config:clear
php artisan migrate --force
php artisan route:cache
php artisan view:cache

# The Artisan commands above run as root, whereas Apache serves requests as
# www-data. Ensure files they created (including laravel.log) stay writable
# after Apache drops privileges.
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

echo "Starting Apache..."
exec apache2-foreground
