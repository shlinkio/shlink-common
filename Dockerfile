FROM composer:2

ENV APCU_VERSION 5.1.24

RUN apk add --no-cache libpng-dev libpng libjpeg-turbo-dev libwebp-dev zlib-dev libxpm-dev linux-headers
RUN docker-php-ext-install gd sockets

RUN apk add --update linux-headers && \
    apk add --no-cache --virtual .phpize-deps ${PHPIZE_DEPS} && \
    pecl install xdebug apcu-${APCU_VERSION} && \
    docker-php-ext-enable xdebug apcu && \
    apk del .phpize-deps
