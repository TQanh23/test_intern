#!/bin/sh
set -e

# Only prepare the app for the real server, not for one-off `docker compose run` commands.
if [ "$1" = "php-fpm" ]; then
    [ -f .env ] || cp .env.example .env

    composer install --no-interaction --no-progress

    if grep -q '^APP_KEY=$' .env; then
        php artisan key:generate --force
    fi

    # php-fpm runs as www-data, which does not own these on a Linux host bind mount.
    chmod -R a+rwX storage bootstrap/cache

    # The default seeder is not idempotent, so it only runs against an empty database.
    if php artisan migrate:status > /dev/null 2>&1; then
        php artisan migrate --force
    else
        php artisan migrate --seed --force
    fi
fi

exec docker-php-entrypoint "$@"
