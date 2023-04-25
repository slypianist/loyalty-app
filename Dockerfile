FROM php:8.0-apache

# Install required extensions
RUN docker-php-ext-install pdo_mysql

# Set document root
WORKDIR /var/www/html

# Copy app files to container
COPY . .

# Set permissions for storage and bootstrap directories
RUN chmod -R 777 storage bootstrap/cache

# Install composer dependencies
#RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN wget https://raw.githubusercontent.com/composer/getcomposer.org/1b137f8bf6db3e79a38a5bc45324414a6b1f9df2/web/installer -O - -q | php -- --quiet
RUN mv composer.phar /usr/local/bin/composer
RUN composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
