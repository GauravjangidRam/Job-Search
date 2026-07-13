#!/bin/bash

# Default port to 80 if Render doesn't provide one
export PORT=${PORT:-80}

# Configure Apache to listen on the correct PORT dynamically at runtime
echo "Listen ${PORT}" > /etc/apache2/ports.conf
sed -i "s/:80/:${PORT}/g" /etc/apache2/sites-available/000-default.conf

echo "Running migrations and caching..."
php artisan config:clear
php artisan migrate --force
php artisan route:cache
php artisan view:cache

echo "Starting Apache..."
exec apache2-foreground
  