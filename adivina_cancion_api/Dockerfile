FROM php:8.2-apache

# Copia todos los archivos del backend dentro del servidor Apache
COPY . /var/www/html/

# Habilitar módulos necesarios
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Dar permisos a Apache
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
