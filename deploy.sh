#!/bin/bash

# -------------------------------
# CONFIGURATION
# -------------------------------
APP_DIR=/var/www/weddingManager       # Chemin vers ton projet sur le serveur
BRANCH=main                           # Branche Git à déployer
PHP_BIN=php                           # Binaire PHP (php ou php8.2)
COMPOSER_BIN=composer                 # Chemin vers composer
ENV=prod

# -------------------------------
# ARRÊT EN CAS D'ERREUR
# -------------------------------
set -e

echo "=== Déploiement de la branche $BRANCH sur $APP_DIR ==="

# -------------------------------
# 1. Aller dans le dossier de l'app
# -------------------------------
cd $APP_DIR

# -------------------------------
# 2. Pull depuis Git
# -------------------------------
echo "Pull de la dernière version..."
git fetch origin $BRANCH
git reset --hard origin/$BRANCH

# -------------------------------
# 3. Installer les dépendances Composer
# -------------------------------
echo "Installation des dépendances..."
$COMPOSER_BIN install --no-dev --optimize-autoloader

# -------------------------------
# 4. Installer les assets (si Webpack Encore)
# -------------------------------
if [ -f package.json ]; then
    echo "Installation des assets frontend..."
    npm install
    npm run build
fi

# -------------------------------
# 5. Cache et migrations
# -------------------------------
echo "Clear et warmup du cache..."
$PHP_BIN bin/console cache:clear --env=$ENV --no-debug
$PHP_BIN bin/console cache:warmup --env=$ENV

echo "Exécution des migrations (si nécessaire)..."
$PHP_BIN bin/console doctrine:migrations:migrate --no-interaction --env=$ENV

# -------------------------------
# 6. Permissions
# -------------------------------
echo "Mise à jour des permissions..."
HTTP_USER=$(ps aux | grep -E 'apache|nginx|php-fpm' | grep -v grep | head -n 1 | awk '{print $1}')
sudo chown -R $HTTP_USER:$HTTP_USER var/
sudo chown -R $HTTP_USER:$HTTP_USER public/

# -------------------------------
# 7. Redémarrage PHP-FPM
# -------------------------------
echo "Redémarrage de PHP-FPM..."
sudo systemctl reload php8.2-fpm || sudo systemctl restart php8.2-fpm

echo "=== Déploiement terminé ==="