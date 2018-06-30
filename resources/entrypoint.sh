#!/bin/bash

echo "Stalker entrypoint script fired up..."

cd /var/www/stalker
composer install
echo "Composer packages installed."

chown -R www-data:www-data /var/www/stalker/storage
chown -R www-data:www-data /var/www/stalker/public
chmod -R 775 /var/www/stalker/storage
chmod -R 775 /var/www/stalker/public
echo "User permissions on docroot & storage configured."

appKey=$(cat /var/www/stalker/.env | grep 'base64')

if [[ ! $appKey ]]; then
    php artisan key:generate
    echo "New application key generated."
fi

echo "Running database migrations.."
php artisan migrate 

echo "=========="
echo "Completed."
echo "=========="