# Image officielle PHP avec Composer inclus
FROM php:8.2-cli

# Installer dépendances système (pour Laravel et PDO MySQL/Postgres)
RUN apt-get update && apt-get install -y \
    unzip git curl libpq-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Créer dossier app
WORKDIR /app

# Copier tout le projet dans le conteneur
COPY . .

# Installer dépendances Laravel
RUN composer install --no-dev --optimize-autoloader

# Générer la clé Laravel (facultatif, sinon via ENV)
RUN php artisan key:generate || true

# Exposer le port attendu par Render
EXPOSE 10000

# Démarrer Laravel
CMD php artisan serve --host 0.0.0.0 --port 10000
