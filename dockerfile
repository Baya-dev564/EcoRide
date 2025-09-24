# =============================================================================
# DOCKERFILE POUR ECORIDE - IMAGE PHP PERSONNALISÉE 
# =============================================================================

FROM php:8.1.10-fpm

# Installer les extensions PHP nécessaires pour MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli

# AJOUT : Extensions MongoDB
RUN apt-get update && apt-get install -y \
    libssl-dev \
    pkg-config \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

# Installer Composer si pas déjà fait
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# Installer les outils de développement (git, curl, zip, unzip)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Définir le répertoire de travail
WORKDIR /var/www

# Copier le code EcoRide
COPY . /var/www/

# Définir les permissions pour le serveur web
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www
