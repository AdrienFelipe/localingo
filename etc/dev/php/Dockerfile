FROM php:8-fpm-alpine

# Override xdebug configuration file.
COPY xdebug.ini /etc/php7/conf.d/00_xdebug.ini

# Install composer.
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# Isntall ssh.
RUN apk add openssh && ssh-keygen -A

# Cleanup.
RUN rm  -rf /tmp/* /var/cache/*

WORKDIR /app
