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

# Create start script that migrates on first run then serves
RUN echo '#!/bin/bash\n\
DB_PATH="/data/database.sqlite"\n\
if [ ! -f "$DB_PATH" ]; then\n\
  echo "First run - creating database..."\n\
  touch "$DB_PATH"\n\
  php artisan migrate --force\n\
  php artisan db:seed --force\n\
  echo "Database ready!"\n\
else\n\
  echo "Database exists - running migrations..."\n\
  php artisan migrate --force\n\
fi\n\
php artisan config:clear\n\
php artisan route:clear\n\
php artisan view:clear\n\
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}\n\
' > /start.sh && chmod +x /start.sh

EXPOSE ${PORT:-8000}

CMD ["/bin/bash", "/start.sh"]
