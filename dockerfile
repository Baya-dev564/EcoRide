FROM php:8.1.10-fpm

# Installation des dépendances nécessaires, y compris libssl-dev pour SSL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libssl-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Extensions PHP pour sql
RUN docker-php-ext-install pdo pdo_mysql

# Installer le driver mongodb avec support SSL activé
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Copier code
COPY . /var/www/html/

# Permissions
RUN chown -R www-data:www-data /var/www/html/ && \
    chmod -R 755 /var/www/html/


