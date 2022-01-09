ARG COMPOSER_FLAGS="--no-interaction --no-progress --ansi"

###### base stage ######
FROM php:8.0-fpm-alpine as base

ARG COMPOSER_FLAGS
ARG COMPOSER_VERSION="2.2.3"

# global dependencies
RUN apk add --no-cache bash fcgi postgresql-dev

# php extensions
RUN apk add --no-cache --virtual .phpize-deps $PHPIZE_DEPS \
    && docker-php-ext-install -j$(getconf _NPROCESSORS_ONLN) pdo_pgsql \
    && apk del .phpize-deps

# local dependencies
RUN curl -fsSL https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=$COMPOSER_VERSION && \
    curl -fsSL https://raw.githubusercontent.com/renatomefi/php-fpm-healthcheck/master/php-fpm-healthcheck \
         -o /usr/local/bin/php-fpm-healthcheck && chmod +x /usr/local/bin/php-fpm-healthcheck && \
    curl -fsSL https://raw.githubusercontent.com/vishnubob/wait-for-it/master/wait-for-it.sh \
         -o /usr/local/bin/wait-for && chmod +x /usr/local/bin/wait-for

# composer environment
ENV COMPOSER_HOME=/opt/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PATH=${PATH}:${COMPOSER_HOME}/vendor/bin:/app/vendor/bin:/app/bin

# custom php config
COPY box/php/php.ini /usr/local/etc/php/
COPY box/php/php-fpm.conf /usr/local/etc/php-fpm.d/zz-custom.conf

WORKDIR /app

###### dev stage ######
FROM base as dev

ARG COMPOSER_FLAGS
ARG PHP_CS_FIXER_VERSION="v3.4.0"
ARG PHPSTAN_VERSION="1.3.3"
ARG COMPOSER_REQUIRE_CHECKER_VERSION="4.0.0"
ARG XDEBUG_ENABLER_VERSION="facd52cdc1a09fe7e82d6188bb575ed54ab2bc72"
ARG XDEBUG_VERSION="3.1.2"

# php extensions
RUN apk add --no-cache --virtual .phpize-deps $PHPIZE_DEPS \
    && pecl install xdebug-$XDEBUG_VERSION \
    && apk del .phpize-deps

# global development deps
RUN curl -fsSL https://gist.githubusercontent.com/stefanotorresi/9f48f8c476b17c44d68535630522a2be/raw/$XDEBUG_ENABLER_VERSION/xdebug \
        -o /usr/local/bin/xdebug && chmod +x /usr/local/bin/xdebug

# global composer dependencies
RUN composer global require \
      friendsofphp/php-cs-fixer:$PHP_CS_FIXER_VERSION \
      phpstan/phpstan:$PHPSTAN_VERSION \
      phpstan/phpstan-beberlei-assert \
      phpstan/phpstan-phpunit \
      maglnet/composer-require-checker:$COMPOSER_REQUIRE_CHECKER_VERSION

# project composer dependencies
COPY composer.* ./
RUN composer install $COMPOSER_FLAGS --no-scripts --no-autoloader

# copy project sources
COPY . ./

# rerun composer to trigger scripts and dump the autoloader
RUN composer install $COMPOSER_FLAGS


###### production stage ######
FROM base

ARG COMPOSER_FLAGS

# project composer dependencies
COPY composer.* ./
RUN composer install $COMPOSER_FLAGS --no-scripts --no-autoloader --no-dev

# copy project sources cherry picking only production files
COPY index.php ./
COPY src ./src

# rerun composer to trigger scripts and dump the autoloader
RUN composer install $COMPOSER_FLAGS --no-dev --optimize-autoloader

HEALTHCHECK --interval=30s --timeout=2s CMD php-fpm-healthcheck

RUN addgroup -S app && adduser -D -G app -S app && chown app:app .
USER app
ENV HOME=/home/app
