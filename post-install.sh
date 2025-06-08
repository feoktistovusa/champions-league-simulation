#!/bin/bash
touch database/database.sqlite
chmod 777 database/database.sqlite
php artisan migrate:fresh --seed --force
php artisan league:init