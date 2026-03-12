# Simple Dockerfile for PHP 8 with Apache and PDO MySQL support
FROM php:8.1-apache

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Copy project files (optional, we also mount the volume in compose)
COPY --chown=www-data:www-data . /var/www/html

# Expose default HTTP port (already exposed by base image)
EXPOSE 80
