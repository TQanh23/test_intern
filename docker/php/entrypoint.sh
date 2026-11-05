#!/bin/sh
set -e

# Only prepare the app for the real server, not for one-off `docker compose run` commands.
if [ "$1" = "php-fpm" ]; then
    # Container dev không có biến APP_ENV (nó chỉ nằm trong file .env). Image prod luôn có (production hoặc staging),
    # đã có vendor, còn APP_KEY và cấu hình lấy từ biến môi trường, nên bỏ qua bước chuẩn bị của dev.
    if [ -z "$APP_ENV" ]; then
        [ -f .env ] || cp .env.example .env

        composer install --no-interaction --no-progress

        if grep -q '^APP_KEY=$' .env; then
            php artisan key:generate --force
        fi
    fi

    # php-fpm runs as www-data, which does not own these on a Linux host bind mount.
    chmod -R a+rwX storage bootstrap/cache

    # Chỉ seed khi database trống, để không tạo lại đề mẫu mà admin đã xóa.
    # Production không seed vì seed tạo tài khoản có mật khẩu mẫu ai cũng biết.
    if php artisan migrate:status > /dev/null 2>&1 || [ "$APP_ENV" = "production" ]; then
        php artisan migrate --force
    else
        php artisan migrate --seed --force
    fi
fi

exec docker-php-entrypoint "$@"
