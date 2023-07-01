FROM php:8.1-fpm

RUN apt update \
    && apt install -y --no-install-recommends zlib1g-dev g++ git libicu-dev zip libzip-dev zip libssl-dev libpq-dev libpng-dev curl unzip libxml2-dev\
    && docker-php-ext-install intl opcache pdo pdo_mysql \
    && pecl install apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-install intl pdo_pgsql pgsql gd pcntl \
    && docker-php-ext-enable apcu pdo_pgsql sodium

WORKDIR /var/www/api

COPY . .

RUN chmod +x installation.sh

RUN chmod 775 /var/www/api -R

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony