FROM php:8.2-apache

# Copia todo el código al servidor Apache
COPY . /var/www/html/

# Exponer el puerto donde corre Apache
EXPOSE 80

# Habilitar módulos necesarios
RUN docker-php-ext-install mysqli pdo pdo_mysql
