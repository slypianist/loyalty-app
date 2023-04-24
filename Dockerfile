FROM php:8.0-fpm

WORKDIR /var/www/html

RUN apt-get update && \
    apt-get install -y \
        libzip-dev \
        zip \
        && docker-php-ext-install zip pdo_mysql

COPY . .

CMD ["php", "artisan", "serve", "--host=0.0.0.0"]
