# Use an official PHP runtime as a parent image
FROM php:8.0-fpm-alpine

# Set the working directory to /app
WORKDIR /app

# Copy the current directory contents into the container at /app
COPY . /app

# Install any dependencies required by your app
RUN apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        && pecl install xdebug \
        && docker-php-ext-enable xdebug \
        && apk del .build-deps

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install app dependencies
RUN composer install --no-dev --no-scripts --no-autoloader

# Generate optimized autoloader
RUN composer dump-autoload --no-dev --optimize

# Expose port 80 to the Docker host
EXPOSE 80

# Run the application
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]
