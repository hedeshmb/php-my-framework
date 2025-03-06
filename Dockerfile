# Use the official PHP image with Apache
FROM php:8.3-apache

# Enable Apache mod_rewrite for URL routing
RUN a2enmod rewrite

# Set the document root to the public folder
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# Install any required PHP extensions (example: mysqli)
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN docker-php-ext-install pdo pdo_mysql

# Copy the application files into the container
COPY . /var/www/html

# Set the working directory
WORKDIR /var/www/html

# Expose port 80 for HTTP traffic
EXPOSE 80