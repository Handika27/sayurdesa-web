FROM php:8.2-cli

# Menginstal driver database MySQL & PDO
RUN docker-php-ext-install pdo pdo_mysql mysqli

WORKDIR /var/www/html
COPY . /var/www/html

EXPOSE 8080

# Menjalankan web server bawaan PHP pada port 8080
CMD ["php", "-S", "0.0.0.0:8080"]