FROM php:8.1.10-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libssl-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql

RUN pecl install mongodb && docker-php-ext-enable mongodb

# CORRECTION : Copier vers /var/www/ (sans html/)
COPY . /var/www/

#  Permissions sur /var/www/ (sans html/)
RUN chown -R www-data:www-data /var/www/ && \
    chmod -R 755 /var/www/

#  Dossier de sessions
RUN mkdir -p /tmp/sessions && \
    chown -R www-data:www-data /tmp/sessions && \
    chmod -R 755 /tmp/sessions
