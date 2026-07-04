FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    bash \
    git \
    curl \
    unzip \
    icu-dev \
    libzip-dev \
    postgresql-dev \
    oniguruma-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    linux-headers

RUN docker-php-ext-configure gd --with-freetype --with-jpeg

RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    intl \
    zip \
    bcmath \
    mbstring \
    gd \
    opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/entrypoint.sh /usr/local/bin/gas-erp-entrypoint.sh
RUN chmod +x /usr/local/bin/gas-erp-entrypoint.sh

WORKDIR /var/www/html

ENTRYPOINT ["/usr/local/bin/gas-erp-entrypoint.sh"]
CMD ["php-fpm"]
