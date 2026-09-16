FROM composer:2 AS vendor

WORKDIR /var/www

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    --ignore-platform-req=ext-intl \
    --optimize-autoloader

FROM node:22-alpine AS assets

WORKDIR /var/www

COPY package.json package-lock.json ./

RUN npm ci --ignore-scripts

COPY --from=vendor /var/www/vendor ./vendor
COPY app ./app
COPY resources ./resources
COPY vite.config.js ./

RUN npm run build

FROM php:8.3-fpm-bookworm AS app

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libicu-dev \
        libonig-dev \
        libxml2-dev \
        libzip-dev \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        xml \
        zip \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www

COPY docker/php/php-production.ini /usr/local/etc/php/conf.d/99-kanban.ini
COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint

RUN chmod +x /usr/local/bin/docker-entrypoint

COPY . .
COPY --from=vendor /var/www/vendor ./vendor
COPY --from=assets /var/www/public/build ./public/build

RUN mkdir -p \
        storage/logs \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        bootstrap/cache \
    && APP_ENV=production APP_DEBUG=false APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= LOG_CHANNEL=stderr php artisan package:discover --ansi \
    && APP_ENV=production APP_DEBUG=false APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= LOG_CHANNEL=stderr php artisan filament:upgrade \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

ENTRYPOINT ["/usr/local/bin/docker-entrypoint"]

CMD ["php-fpm", "-F"]

FROM nginx:1.27-alpine AS web

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=app /var/www/public /var/www/public

RUN mkdir -p /var/www/storage/app/public \
    && ln -s /var/www/storage/app/public /var/www/public/storage

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=5 \
    CMD wget --no-verbose --tries=1 --spider http://127.0.0.1/up || exit 1
