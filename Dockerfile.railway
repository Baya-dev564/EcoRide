# Multi-stage build pour production avec Nginx + PHP-FPM
FROM php:8.1-fpm as php

# Installation des extensions PHP
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Installation de MongoDB extension
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Configuration PHP pour production
RUN echo "memory_limit=256M" >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini && \
    echo "upload_max_filesize=32M" >> /usr/local/etc/php/conf.d/docker-php-uploads.ini && \
    echo "post_max_size=32M" >> /usr/local/etc/php/conf.d/docker-php-uploads.ini

# Copie du code
COPY . /var/www/html/

# Permissions
RUN chown -R www-data:www-data /var/www/html/ && \
    chmod -R 755 /var/www/html/

# Stage final avec Nginx
FROM nginx:alpine as production

# Installation de PHP-FPM dans Nginx
RUN apk add --no-cache php81 php81-fpm php81-pdo php81-pdo_mysql php81-mysqli php81-mongodb

# Copie du code depuis l'étape PHP
COPY --from=php /var/www/html /var/www/html
COPY --from=php /usr/local/etc/php /usr/local/etc/php

# Configuration Nginx pour EcoRide
RUN echo 'server {' > /etc/nginx/conf.d/default.conf && \
    echo '    listen 80;' >> /etc/nginx/conf.d/default.conf && \
    echo '    server_name localhost;' >> /etc/nginx/conf.d/default.conf && \
    echo '    root /var/www/html/public;' >> /etc/nginx/conf.d/default.conf && \
    echo '    index index.php index.html;' >> /etc/nginx/conf.d/default.conf && \
    echo '    ' >> /etc/nginx/conf.d/default.conf && \
    echo '    location / {' >> /etc/nginx/conf.d/default.conf && \
    echo '        try_files $uri $uri/ /index.php?$query_string;' >> /etc/nginx/conf.d/default.conf && \
    echo '    }' >> /etc/nginx/conf.d/default.conf && \
    echo '    ' >> /etc/nginx/conf.d/default.conf && \
    echo '    location ~ \.php$ {' >> /etc/nginx/conf.d/default.conf && \
    echo '        fastcgi_pass 127.0.0.1:9000;' >> /etc/nginx/conf.d/default.conf && \
    echo '        fastcgi_index index.php;' >> /etc/nginx/conf.d/default.conf && \
    echo '        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;' >> /etc/nginx/conf.d/default.conf && \
    echo '        include fastcgi_params;' >> /etc/nginx/conf.d/default.conf && \
    echo '    }' >> /etc/nginx/conf.d/default.conf && \
    echo '}' >> /etc/nginx/conf.d/default.conf

# Script de démarrage pour Nginx + PHP-FPM
RUN echo '#!/bin/sh' > /start.sh && \
    echo 'php-fpm81 -D' >> /start.sh && \
    echo 'nginx -g "daemon off;"' >> /start.sh && \
    chmod +x /start.sh

# Port
EXPOSE 80

# Démarrage
CMD ["/start.sh"]
