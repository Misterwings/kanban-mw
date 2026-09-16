#!/bin/sh

set -eu

cd /var/www

mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

exec "$@"
