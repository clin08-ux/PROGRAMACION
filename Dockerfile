FROM php:8.3-apache

RUN docker-php-ext-install mysqli pdo_mysql

COPY . /var/www/html/

ENV PORT=10000
RUN sed -i 's/80/${PORT}/g' \
 /etc/apache2/sites-available/000-default.conf \
 /etc/apache2/ports.conf