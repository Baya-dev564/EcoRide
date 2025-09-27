FROM php:8.1.10

# Installation des dépendances PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Extensions PHP pour PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Copier code
COPY . /var/www/html/

# Permissions
RUN chown -R www-data:www-data /var/www/html/ && \
    chmod -R 755 /var/www/html/

# Port
EXPOSE 80

CMD ["sh","-c","php -S 0.0.0.0:${PORT:-8000} -t /var/www/html/public"]

