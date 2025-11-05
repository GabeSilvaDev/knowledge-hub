FROM php:8.4-cli AS base

ARG DEBIAN_FRONTEND=noninteractive
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_NO_INTERACTION=1

RUN apt-get update && apt-get install -y --no-install-recommends \
    autoconf \
    automake \
    build-essential \
    curl \
    git \
    libicu-dev \
    libonig-dev \
    libssl-dev \
    libxml2-dev \
    libzip-dev \
    pkg-config \
    unzip \
    zip \
    zlib1g-dev \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        intl \
        pcntl \
        zip \
    && pecl install mongodb redis xdebug \
    && docker-php-ext-enable mongodb redis xdebug \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

RUN echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=no" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini \
    && echo "memory_limit=512M" >> /usr/local/etc/php/php.ini \
    && echo "upload_max_filesize=20M" >> /usr/local/etc/php/php.ini \
    && echo "post_max_size=20M" >> /usr/local/etc/php/php.ini \
    && echo "max_execution_time=60" >> /usr/local/etc/php/php.ini

COPY --chown=www-data:www-data . /var/www

RUN composer install --optimize-autoloader --no-dev --prefer-dist || true \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache \
    && echo '#!/bin/sh\n\
set -e\n\
sleep 3\n\
composer install --no-interaction --optimize-autoloader || true\n\
php artisan key:generate --ansi || true\n\
php artisan config:clear || true\n\
php artisan cache:clear || true\n\
exec php artisan serve --host=0.0.0.0 --port=8004\n\
' > /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8004

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
