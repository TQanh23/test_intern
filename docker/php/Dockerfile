# Build context là gốc repo. Dev chỉ dùng stage base, code được bind mount vào.
FROM php:8.3-fpm-alpine AS base

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN apk add --no-cache git \
    && install-php-extensions pdo_mysql mongodb bcmath intl zip pcntl gd opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --chmod=0755 docker/php/entrypoint.sh /usr/local/bin/app-entrypoint

ENTRYPOINT ["app-entrypoint"]
CMD ["php-fpm"]

# Build frontend một lần lúc build image, không build lúc khởi động như dev.
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# Image production: code, vendor không có package dev và asset đã build nằm sẵn trong image.
FROM base AS prod
# Cài dependency trước khi copy code để layer này còn cache khi chỉ sửa code.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress
COPY . .
COPY --from=assets /app/public/build public/build
RUN composer dump-autoload --optimize --no-dev --no-interaction \
    && chown -R www-data:www-data storage bootstrap/cache
ENV APP_ENV=production

# Nginx chỉ cần thư mục public để trả file tĩnh, còn file .php chuyển sang app:9000.
FROM nginx:1.27-alpine AS web
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=prod /var/www/html/public /var/www/html/public
