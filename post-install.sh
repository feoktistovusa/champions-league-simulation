#!/bin/bash
# Create database directory if it doesn't exist
mkdir -p database
touch database/database.sqlite
chmod 644 database/database.sqlite

# Generate app key if not set
php artisan key:generate --force

# Clear and cache config
php artisan config:clear
php artisan config:cache

# Run migrations and seed
php artisan migrate:fresh --seed --force

# Initialize league
php artisan league:init