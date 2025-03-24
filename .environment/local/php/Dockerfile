FROM php:8.4-fpm-alpine3.21

RUN set -ex \
  && apk --no-cache add \
    libzip-dev \
    bash \
    zip

RUN apk add libzip-dev
RUN docker-php-ext-install zip

RUN apk add --update linux-headers

RUN apk --update --no-cache add autoconf g++ make && \
     pecl install -f redis && \
     docker-php-ext-enable redis && \
     apk del --purge autoconf g++ make

RUN docker-php-ext-configure opcache --enable-opcache \
    && docker-php-ext-install opcache

RUN curl -sL https://getcomposer.org/installer | php -- --install-dir /usr/bin --filename composer

WORKDIR /app

CMD ["php-fpm"]