FROM php:8-fpm-alpine AS base
WORKDIR /app


FROM base AS dev

# Override xdebug configuration file.
COPY xdebug.ini /etc/php7/conf.d/00_xdebug.ini

# Install composer.
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# Install ssh.
RUN apk add openssh && ssh-keygen -A

# Install yarn.
RUN apk add yarn


FROM base AS prod

COPY active-framework /app
COPY src /app/src

# Cleanup.
RUN rm  -rf /tmp/* /var/cache/*
