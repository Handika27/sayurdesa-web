FROM php:8.2-apache

# Mengaktifkan ekstensi database untuk PHP
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Menyalin file proyek ke direktori web Apache
COPY . /var/www/html/

# Mengatur port Apache agar otomatis membaca port dari Railway ($PORT)
ENV PORT=8080
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

EXPOSE 8080