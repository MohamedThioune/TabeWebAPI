#!/bin/bash
set -e

#php artisan migrate --force
#php artisan config:cache
#php artisan route:cache
#php artisan optimize:clear

# Start php-fpm in the background
php-fpm -D

# Nginx in the foreground (keeps the container alive)
nginx -g "daemon off;"