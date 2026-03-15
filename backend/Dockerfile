FROM php:8.2-apache

RUN a2dismod mpm_event && a2enmod mpm_prefork

COPY . /var/www/html/

RUN docker-php-ext-install mysqli pdo pdo_mysql

EXPOSE 80

CMD ["apache2-foreground"]