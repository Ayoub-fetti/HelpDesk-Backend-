#!/bin/sh

# Wait for database to be ready
echo "Waiting for database..."
while ! nc -z postgres 5432; do
  sleep 1
done
echo "Database is ready!"

# Generate application key if not set
php artisan key:generate --force

# Optimize (clear and rebuild caches)
php artisan optimize

# Run migrations and seed database
php artisan migrate --seed --force

echo "Starting Laravel server..."
# Start the Laravel server
exec php artisan serve --host=0.0.0.0 --port=8000

