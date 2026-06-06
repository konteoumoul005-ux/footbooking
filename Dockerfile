FROM php:8.2-apache

RUN apt-get update && apt-get install -y libpq-dev

RUN docker-php-ext-install pdo pdo_pgsql

RUN a2enmod rewrite

RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

COPY . /var/www/html/

EXPOSE 80