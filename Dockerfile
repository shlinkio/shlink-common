FROM composer:2

ENV APCU_VERSION 5.1.21

RUN apk add --no-cache libpng-dev libpng libjpeg-turbo-dev libwebp-dev zlib-dev libxpm-dev linux-headers
RUN docker-php-ext-install gd sockets

RUN apk add --no-cache --virtual .phpize-deps ${PHPIZE_DEPS} && \
    pecl install pcov apcu-${APCU_VERSION} && \
    docker-php-ext-enable pcov apcu && \
    apk del .phpize-deps
