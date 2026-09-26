FROM php:8.5-fpm-alpine@sha256:ef8e5dac4f891df1e452a7b89b01de01d20d25788d2c3dd0f8ce137ac546c47d
SHELL ["/bin/ash", "-o", "pipefail", "-c"]

# Latest release should be periodically revalidated against aptible/supercronic.
ENV SUPERCRONIC_VERSION=0.2.49
ENV SUPERCRONIC_URL=https://github.com/aptible/supercronic/releases/download/v${SUPERCRONIC_VERSION}/supercronic-linux-amd64 \
    SUPERCRONIC_SHA1SUM=e63c11a9726b775a6a11801e81af4f3fb926aa68 \
    SUPERCRONIC=supercronic-linux-amd64

# Fuzzy pins (=~) hold the feature version but tolerate Alpine -rN security
# revisions; exact pins rot as soon as Alpine drops the old revision (same
# policy as Dockerfile.pinboard).
RUN apk add --no-cache \
    bash=~5.3 \
    curl=~8.22 \
    git=~2.54 \
    icu-dev=~78 \
    libzip-dev=~1.11 \
    libxslt-dev=~1.1 \
    oniguruma-dev=~6.9 \
    tzdata=~2026 \
    unzip=~6.0 \
    zip=~3.0 \
 && docker-php-ext-install -j"$(nproc)" pdo_mysql intl xsl
RUN curl -fsSLO "${SUPERCRONIC_URL}" \
 && echo "${SUPERCRONIC_SHA1SUM}  ${SUPERCRONIC}" | sha1sum -c - \
 && chmod +x "${SUPERCRONIC}" \
 && mv "${SUPERCRONIC}" /usr/local/bin/${SUPERCRONIC} \
 && ln -s /usr/local/bin/${SUPERCRONIC} /usr/local/bin/supercronic

ARG INSTALL_XDEBUG=false
RUN if [ "${INSTALL_XDEBUG}" = "true" ]; then \
      pecl install xdebug && docker-php-ext-enable xdebug; \
    fi
COPY ./docker/php-fpm/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

COPY --from=composer:2@sha256:9715c7f69044da2a212a5fbde29ee7da24e364d426560ae6367b060236f847d7 /usr/bin/composer /usr/local/bin/composer

ARG PUID=1000
ARG PGID=1000
RUN deluser www-data 2>/dev/null || true \
 && delgroup www-data 2>/dev/null || true \
 && addgroup -g "${PGID}" -S www-data \
 && adduser -u "${PUID}" -S -D -G www-data www-data

WORKDIR /var/www

COPY ./docker/php-fpm/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm", "-F"]
