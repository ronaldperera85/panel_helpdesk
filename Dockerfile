# Usamos la imagen oficial de PHP con Apache (versión 8.2 es estable y moderna)
FROM php:8.2-apache

# Instalamos librerías del sistema necesarias para cURL
# (data.php usa curl_init, así que esto es OBLIGATORIO)
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    && docker-php-ext-install curl

# (Opcional) Habilitamos mod_rewrite de Apache por si quisieras URLs amigables en el futuro
RUN a2enmod rewrite

# Copiamos todos tus archivos (index.html, data.php, etc) al directorio público del contenedor
COPY . /var/www/html/

# Ajustamos permisos para que Apache (www-data) pueda leer los archivos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Exponemos el puerto 80 para el tráfico web
EXPOSE 80