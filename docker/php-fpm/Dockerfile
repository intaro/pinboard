FROM php:8.5-fpm-alpine

RUN apk add --no-cache \
    bash \
    curl \
    git \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    tzdata \
    unzip \
    zip \
 && docker-php-ext-install -j"$(nproc)" pdo_mysql intl

ARG INSTALL_XDEBUG=false
RUN if [ "${INSTALL_XDEBUG}" = "true" ]; then \
      pecl install xdebug && docker-php-ext-enable xdebug; \
    fi
COPY ./docker/php-fpm/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

ARG PUID=1000
ARG PGID=1000
RUN addgroup -g "${PGID}" -S www-data || true && \
    adduser -u "${PUID}" -S -D -G www-data www-data || true

WORKDIR /var/www

COPY ./docker/php-fpm/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm", "-F"]
