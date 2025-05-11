#!/usr/bin/env bash

composer install --no-dev
php artisan key:generate
php artisan config:cache
php artisan migrate --force
