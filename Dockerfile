FROM composer:2

ENV APCU_VERSION 5.1.27

RUN apk add --no-cache libpng-dev libpng libjpeg-turbo-dev libwebp-dev zlib-dev libxpm-dev linux-headers
RUN docker-php-ext-install gd sockets

COPY --from=ghcr.io/php/pie:bin /pie /usr/bin/pie
RUN apk add --update linux-headers && \
    apk add --no-cache --virtual .phpize-deps ${PHPIZE_DEPS} && \
    pecl install apcu-${APCU_VERSION} && \
    docker-php-ext-enable apcu && \
    pie install xdebug/xdebug && \
    apk del .phpize-deps
