FROM php:8.1-apache

# Extensions PHP
RUN docker-php-ext-install pdo pdo_mysql mysqli
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Modules Apache
RUN a2enmod rewrite headers

# Configuration Apache pour /public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Copier code
COPY . /var/www/html/

# Permissions
RUN chown -R www-data:www-data /var/www/html/ && \
    chmod -R 755 /var/www/html/

# Port
EXPOSE 80

# Commande de démarrage
CMD ["apache2-foreground"]
