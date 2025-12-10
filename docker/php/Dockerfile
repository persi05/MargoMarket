FROM php:8.3-fpm-alpine

ENV RUN_DEPS \
    zlib \
    libzip \
    libpng \
    libjpeg-turbo \
    postgresql-libs \
    icu-libs

ENV BUILD_DEPS \
    zlib-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    postgresql-dev \
    icu-dev

ENV PHP_EXTENSIONS \
    opcache \
    zip \
    gd \
    bcmath \
    pgsql \
    pdo_pgsql \
    intl

RUN apk add --no-cache --virtual .build-deps $BUILD_DEPS \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" $PHP_EXTENSIONS \
    && apk del .build-deps

RUN apk add --no-cache --virtual .run-deps $RUN_DEPS

RUN echo 'memory_limit = 256M' >> /usr/local/etc/php/conf.d/memory.ini \
    && echo 'upload_max_filesize = 10M' >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo 'post_max_size = 30M' >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo 'session.save_path = "/tmp"' >> /usr/local/etc/php/conf.d/sessions.ini

WORKDIR /var/www/html

COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html

VOLUME ["/var/www/html"]
