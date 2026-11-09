FROM php:8.3-apache

RUN docker-php-ext-install mysqli pdo_mysql

COPY . /var/www/html/

# Render asigna el puerto en la variable PORT (10000 por defecto)
ENV PORT=10000
RUN sed -i 's/Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf \
 && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/' /etc/apache2/sites-available/000-default.conf

# Carpeta donde se guardan las fotos subidas desde el CRUD
RUN mkdir -p /var/www/html/uploads \
 && chown -R www-data:www-data /var/www/html/uploads

EXPOSE 10000
