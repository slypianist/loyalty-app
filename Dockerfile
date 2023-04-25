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
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer1 install --no-interaction --no-dev --prefer-dist --optimize-autoloader

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
