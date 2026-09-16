FROM php:8.3-fpm

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libicu-dev libxml2-dev libonig-dev libzip-dev \
    && docker-php-ext-install -j2 intl mbstring pdo_mysql zip xml \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY php.ini /usr/local/etc/php/conf.d/99-kanban.ini
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint

RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]

CMD ["php-fpm"]
