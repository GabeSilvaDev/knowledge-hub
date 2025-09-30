FROM php:8.4-cli

ARG DEBIAN_FRONTEND=noninteractive

# dependências + extensões
RUN apt-get update && apt-get install -y \
    git unzip curl zip libzip-dev libicu-dev zlib1g-dev libonig-dev libxml2-dev \
    pkg-config libssl-dev autoconf automake build-essential \
    && docker-php-ext-install bcmath intl pcntl zip \
    && pecl install mongodb xdebug \
    && docker-php-ext-enable mongodb xdebug \
    && rm -rf /var/lib/apt/lists/*

# composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Configuração mínima do Xdebug para coverage
RUN echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.start_with_request=no" >> /usr/local/etc/php/conf.d/xdebug.ini

EXPOSE 8004

CMD ["sh", "-c", "composer install --no-interaction || true && php artisan key:generate --ansi || true && php artisan serve --host=0.0.0.0 --port=8004"]
