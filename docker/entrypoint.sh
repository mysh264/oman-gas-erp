#!/bin/sh
set -eu

for path in /var/www/html/storage /var/www/html/bootstrap/cache; do
    if [ -e "$path" ]; then
        chown -R www-data:www-data "$path"
        chmod -R ug+rwX "$path"
    fi
done

exec "$@"
