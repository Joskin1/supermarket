ARG PHP_VERSION=8.5
ARG NODE_VERSION=22

FROM composer:2 AS composer

FROM php:${PHP_VERSION}-fpm-bookworm AS php-base

ARG APP_UID=1000
ARG APP_GID=1000

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=0 \
    PHP_OPCACHE_MAX_ACCELERATED_FILES=20000 \
    PHP_OPCACHE_MEMORY_CONSUMPTION=192 \
    PHP_OPCACHE_INTERNED_STRINGS_BUFFER=16

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        git \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libxml2-dev \
        libzip-dev \
        supervisor \
        unzip \
        zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        opcache \
        pcntl \
        pdo_mysql \
        sockets \
        zip \
    && apt-get purge -y --auto-remove \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libxml2-dev \
        libzip-dev \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN groupmod -g "${APP_GID}" www-data \
    && usermod -u "${APP_UID}" -g "${APP_GID}" www-data

FROM php-base AS php-deps

ARG FLUX_USERNAME=
ARG FLUX_LICENSE_KEY=

COPY composer.json composer.lock ./
RUN if [ -n "$FLUX_USERNAME" ] && [ -n "$FLUX_LICENSE_KEY" ]; then \
        composer config http-basic.composer.fluxui.dev "$FLUX_USERNAME" "$FLUX_LICENSE_KEY"; \
    fi \
    && composer install \
        --no-dev \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader \
        --no-scripts \
    && composer clear-cache \
    && rm -f auth.json

FROM node:${NODE_VERSION}-bookworm-slim AS assets
WORKDIR /app

COPY package.json package-lock.json vite.config.js ./
COPY resources ./resources
COPY public ./public
COPY --from=php-deps /var/www/html/vendor ./vendor

RUN npm ci && npm run build

FROM php-base AS app

COPY . .
COPY --from=php-deps /var/www/html/vendor ./vendor
COPY --from=assets /app/public/build ./public/build
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-white-mart.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/99-opcache.ini
COPY docker/php/supervisord.conf /etc/supervisor/conf.d/white-mart.conf
COPY docker/php/entrypoint.sh /usr/local/bin/white-mart-entrypoint
COPY docker/php/healthcheck.sh /usr/local/bin/white-mart-healthcheck

RUN chmod +x /usr/local/bin/white-mart-entrypoint /usr/local/bin/white-mart-healthcheck \
    && mkdir -p \
        storage/app/private \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown www-data:www-data /var/www/html public \
    && chown -R www-data:www-data storage bootstrap/cache public/build \
    && composer dump-autoload --optimize --no-dev --no-interaction \
    && php artisan package:discover --ansi

USER www-data

EXPOSE 9000

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD white-mart-healthcheck

ENTRYPOINT ["white-mart-entrypoint"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/white-mart.conf"]

FROM nginx:1.27-alpine AS nginx

WORKDIR /var/www/html

COPY public ./public
COPY --from=assets /app/public/build ./public/build
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
