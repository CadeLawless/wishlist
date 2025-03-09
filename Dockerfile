# Use the official PHP with Apache image
FROM php:8.1-apache

# Install system dependencies for Composer
RUN apt-get update && apt-get install -y \
    zlib1g-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install zip mysqli \
    && docker-php-ext-enable mysqli

# Install Composer globally
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Set working directory for composer install
WORKDIR /var/www

# Copy composer files first to leverage Docker caching
COPY composer.json composer.lock ./

# Install dependencies before copying app source code
RUN composer install --no-dev --optimize-autoloader

# Copy the rest of the application source code
COPY src/ /var/www/src/
COPY public/ /var/www/html/

# Set permissions for Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Reset the working directory
WORKDIR /var/www/html

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]