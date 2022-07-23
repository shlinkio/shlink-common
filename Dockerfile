FROM composer:2

ENV APCU_VERSION 5.1.21

RUN apk add --no-cache libpng-dev libpng libjpeg-turbo-dev libwebp-dev zlib-dev libxpm-dev
RUN docker-php-ext-install gd sockets

# Install APCu extension
ADD https://pecl.php.net/get/apcu-$APCU_VERSION.tgz /tmp/apcu.tar.gz
RUN mkdir -p /usr/src/php/ext/apcu \
  && tar xf /tmp/apcu.tar.gz -C /usr/src/php/ext/apcu --strip-components=1 \
  && docker-php-ext-configure apcu \
  && docker-php-ext-install apcu \
  && rm /tmp/apcu.tar.gz \
  && rm /usr/local/etc/php/conf.d/docker-php-ext-apcu.ini \
  && echo extension=apcu.so > /usr/local/etc/php/conf.d/20-php-ext-apcu.ini
