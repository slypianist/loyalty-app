FROM php:8.0-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libonig-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install \
    intl \
    pdo_mysql \
    zip

# Set the working directory
WORKDIR /var/www/html

# Copy the app files to the working directory
COPY . .

# Install dependencies with Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-interaction --prefer-dist --no-scripts --no-dev

# Set the permissions for the storage and bootstrap/cache directories
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# Expose port 80 for Apache
EXPOSE 80
