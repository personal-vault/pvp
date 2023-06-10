FROM ghcr.io/roadrunner-server/roadrunner:2023.1.3 AS roadrunner
FROM php:8-fpm

WORKDIR /var/www

COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr

# Install the Postgres PDO extension
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    zip \
    unzip \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY ./config/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Install and configure the application
RUN docker-php-ext-configure sockets \
	&& docker-php-ext-install -j$(nproc) sockets

# Install exiftool
RUN curl https://exiftool.org/Image-ExifTool-12.62.tar.gz -o /tmp/exiftool.tar.gz \
    && tar -xzf /tmp/exiftool.tar.gz -C /tmp \
    && cd /tmp/Image-ExifTool-12.62 \
    && perl Makefile.PL \
    && make \
    && make install \
    && rm -rf /tmp/exiftool.tar.gz /tmp/Image-ExifTool-12.62

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

CMD composer install && rr serve -c /etc/roadrunner/.rr.yaml
