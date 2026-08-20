FROM php:8.2-apache

# Menginstal driver database
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Menyalin seluruh file proyek ke web root Apache
COPY . /var/www/html/

# Mengatur agar Apache menggunakan port dinamis dari Railway
RUN sed -i 's/80/8080/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

EXPOSE 8080