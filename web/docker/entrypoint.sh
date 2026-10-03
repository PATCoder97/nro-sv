#!/bin/sh
set -eu

mkdir -p /var/www/html/logs /var/www/html/uploads
chown -R www-data:www-data /var/www/html/logs /var/www/html/uploads /var/www/html/images

php /var/www/html/docker/migrate.php

exec docker-php-entrypoint "$@"
