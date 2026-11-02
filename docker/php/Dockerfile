FROM php:8.3-fpm-alpine

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN apk add --no-cache git \
    && install-php-extensions pdo_mysql mongodb bcmath intl zip pcntl gd opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
