FROM php:8.4-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libsqlite3-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_sqlite mbstring exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Node.js 20 (required for Vite)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure Apache virtual host properly
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
        Options -Indexes +FollowSymLinks\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy package files first for better caching
COPY package.json package-lock.json ./
RUN npm ci

# Copy composer files
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy all application files
COPY . .

# Run composer scripts
RUN composer dump-autoload --optimize

# Build frontend assets
RUN npm run build && rm -rf node_modules

# Verify build output exists
RUN ls -la public/build/assets/ && echo "Build assets OK"

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create SQLite database file
RUN mkdir -p database && touch database/database.sqlite && chown www-data:www-data database/database.sqlite

EXPOSE 80

# Start script
CMD php artisan migrate --force && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    apache2-foreground
