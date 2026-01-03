# FrankenPHP (avec Caddy intégré) - version stable PHP 8.3
FROM dunglas/frankenphp:php8.3

WORKDIR /app

# Extensions PHP souvent nécessaires pour Symfony + MySQL
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev \
 && docker-php-ext-install intl opcache pdo pdo_mysql zip \
 && rm -rf /var/lib/apt/lists/*

# Copier le projet
COPY . /app

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Installer dépendances PROD
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# Permissions (Symfony)
RUN mkdir -p var/cache var/log && chmod -R 777 var

# Caddy/FrankenPHP écoutera sur le port Railway
ENV APP_ENV=prod
ENV APP_DEBUG=0

# Lancer FrankenPHP avec le Caddyfile du projet
CMD ["frankenphp", "run", "--config", "/app/Caddyfile"]
