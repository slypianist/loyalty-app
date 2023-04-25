FROM php:8.0-fpm

RUN apt-get update && \
    apt-get install -y \
        libzip-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        zip \
        unzip \
        git \
        && docker-php-ext-install pdo_mysql \
        && docker-php-ext-install zip \
        && docker-php-ext-configure gd --with-freetype --with-jpeg \
        && docker-php-ext-install -j$(nproc) gd

WORKDIR /app
COPY . /app

RUN chmod -R 777 storage && \
    chmod -R 777 bootstrap/cache && \
    chown -R www-data:www-data /app

RUN curl --silent --show-error https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install --prefer-dist --no-scripts --no-dev --no-autoloader && \
    rm -rf ~/.composer/cache

RUN composer dump-autoload --no-scripts --no-dev --optimize

CMD ["php-fpm"]
