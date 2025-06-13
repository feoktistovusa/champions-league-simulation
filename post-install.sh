#!/bin/bash
set -e

echo "Creating production .env file..."
# Create production environment file
cat > .env << 'EOF'
APP_NAME="Champions League Simulation"
APP_ENV=production
APP_DEBUG=false
APP_KEY=
APP_URL=${RAILWAY_PUBLIC_DOMAIN:-http://localhost}

LOG_CHANNEL=stderr
LOG_LEVEL=error

DB_CONNECTION=sqlite
DB_DATABASE=/app/database/database.sqlite

SESSION_DRIVER=file
SESSION_LIFETIME=120

CACHE_STORE=file
QUEUE_CONNECTION=sync

BROADCAST_CONNECTION=log
MAIL_MAILER=log
EOF

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