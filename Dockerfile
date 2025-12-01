FROM php:8.3-apache

# Install mysqli extension for MySQL database connectivity
RUN docker-php-ext-install mysqli

# Copy application files to the web server's root directory
COPY . /var/www/html/

# Set appropriate permissions for the web server to read/write files
RUN chown -R www-data:www-data /var/www/html
