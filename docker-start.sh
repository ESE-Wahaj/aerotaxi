#!/bin/bash

DB_PATH="${DB_DATABASE:-/data/database.sqlite}"
TURSO_URL="${TURSO_HTTP_URL:-}"
TURSO_TOKEN="${TURSO_AUTH_TOKEN:-}"

mkdir -p "$(dirname "$DB_PATH")"

if [ ! -f "$DB_PATH" ]; then
    echo "First run - creating database..."
    touch "$DB_PATH"
    php artisan migrate --force
    php artisan db:seed --force
    echo "Database ready!"
else
    echo "Database exists - running any new migrations..."
    php artisan migrate --force 2>/dev/null || true
fi

# Sync tables to Turso if configured
if [ -n "$TURSO_URL" ] && [ -n "$TURSO_TOKEN" ]; then
    echo "Syncing data to Turso..."
    php artisan turso:sync 2>/dev/null || echo "Turso sync skipped"
fi

php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "Starting server on port ${PORT:-8000}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
