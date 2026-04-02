#!/bin/bash
set -e

DB_PATH="${DB_DATABASE:-/data/database.sqlite}"

echo "=== AeroTAXI Starting ==="
echo "DB_PATH: $DB_PATH"

# Ensure directory exists
mkdir -p "$(dirname "$DB_PATH")"

# Check if volume has existing database
if [ -f "$DB_PATH" ] && [ -s "$DB_PATH" ]; then
    echo "Database found at $DB_PATH ($(du -h "$DB_PATH" | cut -f1))"
    echo "Running new migrations if any..."
    php artisan migrate --force 2>&1 || true
else
    echo "No database found - creating fresh..."
    touch "$DB_PATH"
    php artisan migrate --force
    php artisan db:seed --force
    echo "Database created and seeded!"
fi

# Sync to Turso
if [ -n "${TURSO_HTTP_URL:-}" ] && [ -n "${TURSO_AUTH_TOKEN:-}" ]; then
    echo "=== Syncing to Turso ==="
    php artisan turso:sync 2>&1 || echo "WARNING: Turso sync failed"
else
    echo "Turso not configured - skipping sync"
fi

php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "=== Server starting on port ${PORT:-8000} ==="
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
