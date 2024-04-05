#!/usr/bin/env sh

php artisan migrate:fresh --seed
php artisan key:generate

php artisan octane:frankenphp