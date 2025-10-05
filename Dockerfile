FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev libicu-dev libpng-dev nginx supervisor \
    && docker-php-ext-install pdo pdo_pgsql intl zip bcmath gd opcache \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN rm -f /etc/nginx/sites-enabled/default

COPY docker/php/php.ini /usr/local/etc/php/conf.d/php-custom.ini
RUN { \
    echo "opcache.enable=1"; \
    echo "opcache.enable_cli=0"; \
    echo "opcache.memory_consumption=192"; \
    echo "opcache.interned_strings_buffer=16"; \
    echo "opcache.max_accelerated_files=20000"; \
    echo "opcache.validate_timestamps=0"; \
} > /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www/html
COPY src/ /var/www/html/

RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --classmap-authoritative \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

RUN mkdir -p public/vendor/swagger-api/swagger-ui/dist \
    && cp -R vendor/swagger-api/swagger-ui/dist/* public/vendor/swagger-api/swagger-ui/dist/

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && php artisan l5-swagger:generate

COPY docker/nginx/default.prod.conf /etc/nginx/conf.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 80
CMD ["supervisord","-n","-c","/etc/supervisor/conf.d/supervisord.conf"]
