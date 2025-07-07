#!/usr/bin/env bash
cp php.ini /opt/render/project/src/

composer install --no-dev
php artisan key:generate
php artisan config:cache
php artisan migrate --force
