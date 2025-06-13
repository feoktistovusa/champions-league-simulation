#!/bin/bash
set -e

echo "Setting up database..."
# Create database directory if it doesn't exist
mkdir -p database storage/app storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
touch database/database.sqlite
chmod 775 database/database.sqlite
chmod -R 775 storage

echo "Generating app key..."
# Generate app key if not set
php artisan key:generate --force

echo "Clearing and caching config..."
# Clear and cache config
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "Running migrations..."
# Run migrations and seed
php artisan migrate:fresh --seed --force

echo "Initializing league..."
# Initialize league
php artisan league:init

echo "Setup complete!"