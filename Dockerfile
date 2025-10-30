FROM php:8.4-alpine

USER root

RUN apk add --no-cache git

WORKDIR /app

COPY ./recipes ./recipes
COPY ./src ./src
COPY ./composer.json ./

# Copy the composer command to this images by using the composer images with latest tag
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN chmod +x /usr/bin/composer

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

# Clear cache before the installation to avoid conflict
RUN composer clear-cache

RUN composer install --prefer-dist --no-interaction --no-progress --no-scripts --optimize-autoloader