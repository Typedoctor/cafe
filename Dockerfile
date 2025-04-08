FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set the working directory to Laravel
WORKDIR /var/www/html

# Copy existing Laravel project files
COPY . /var/www/html

# Expose port 80 for Apache
EXPOSE 80

# Start Apache when the container runs
CMD ["apache2-foreground"]
