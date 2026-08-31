FROM php:8.3-fpm-alpine AS base
WORKDIR /app


FROM base AS dev

# Install and enable xdebug.
RUN apk add --no-cache $PHPIZE_DEPS linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug
COPY xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN touch /var/log/xdebug.log && chmod go+w /var/log/xdebug.log

# Install composer.
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# Install ssh.
RUN apk add openssh && ssh-keygen -A

# Install yarn.
RUN apk add yarn


FROM base AS prod

COPY active-framework /app
COPY src /app/src

# Cleanup.
RUN rm  -rf /tmp/* /var/cache/*
