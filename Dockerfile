# Use an official PHP image with Apache as the base image.
FROM php:8.2-apache

# Set environment variables.
ENV ACCEPT_EULA=Y

# Install system dependencies.
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules required for Laravel.
RUN a2enmod rewrite

# Set the Apache document root
ENV APACHE_DOCUMENT_ROOT=/var/www/html

# Copy and enable custom Apache config
COPY apache-config.conf /etc/apache2/sites-available/apache-config.conf
RUN a2ensite apache-config.conf && a2dissite 000-default.conf

# Install PHP extensions.
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql

# # Install Composer globally...
# RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Create a directory for your Laravel application.
WORKDIR /var/www/html

# Copy the Laravel application files into the container.
COPY . .

RUN ls -a

# Install Laravel dependencies using Composer.
RUN composer install --no-interaction --optimize-autoloader

# Set permissions for Laravel.
RUN chown -R www-data:www-data storage bootstrap/cache

# Expose port 80 for Apache.
EXPOSE 80