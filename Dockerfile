FROM php:8.3-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libsqlite3-dev libonig-dev \
    && docker-php-ext-install pdo pdo_sqlite mbstring zip opcache \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy app
WORKDIR /var/www/html
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set permissions
RUN chmod -R 775 storage bootstrap/cache

# Create SQLite database and run migrations
RUN touch database/database.sqlite \
    && php artisan config:clear \
    && php artisan migrate --force \
    && php artisan db:seed --force \
    && php artisan view:clear \
    && php artisan route:clear

EXPOSE ${PORT:-8000}

CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
