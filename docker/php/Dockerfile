FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libonig-dev libzip-dev libicu-dev libpng-dev \
    && docker-php-ext-install pdo pdo_pgsql intl zip bcmath gd \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV PHP_MEMORY_LIMIT=512M
COPY php.ini /usr/local/etc/php/conf.d/php-custom.ini

WORKDIR /var/www/html
