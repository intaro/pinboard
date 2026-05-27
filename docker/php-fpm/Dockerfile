FROM php:8.5-fpm-alpine@sha256:82dd8cfd2aa93a98b0357e4c810f894c4ca265b5034aef3be654faae5f579487
SHELL ["/bin/ash", "-o", "pipefail", "-c"]

# Latest release should be periodically revalidated against aptible/supercronic.
ENV SUPERCRONIC_VERSION=0.2.46
ENV SUPERCRONIC_URL=https://github.com/aptible/supercronic/releases/download/v${SUPERCRONIC_VERSION}/supercronic-linux-amd64 \
    SUPERCRONIC_SHA1SUM=5bcefed628e32adc08e32634db2d10e9230dbca0 \
    SUPERCRONIC=supercronic-linux-amd64

RUN apk add --no-cache \
    bash=5.3.3-r1 \
    curl=8.19.0-r0 \
    git=2.52.0-r0 \
    icu-dev=76.1-r1 \
    libzip-dev=1.11.4-r1 \
    oniguruma-dev=6.9.10-r0 \
    tzdata=2026b-r0 \
    unzip=6.0-r16 \
    zip=3.0-r13 \
 && docker-php-ext-install -j"$(nproc)" pdo_mysql intl
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

COPY --from=composer:2@sha256:b09bccd91a78fe8a9ab4b33d707b862e8fe54fec17782e32683ad2a69c46867d /usr/bin/composer /usr/local/bin/composer

ARG PUID=1000
ARG PGID=1000
RUN addgroup -g "${PGID}" -S www-data || true && \
    adduser -u "${PUID}" -S -D -G www-data www-data || true

WORKDIR /var/www

COPY ./docker/php-fpm/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm", "-F"]
