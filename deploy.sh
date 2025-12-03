#!/bin/bash

# -------------------------------
# CONFIGURATION
# -------------------------------
APP_DIR=/var/www/weddingManager       # Chemin vers ton projet sur le serveur
BRANCH=main                           # Branche Git à déployer
PHP_BIN=php                           # Binaire PHP (php ou php8.2)
COMPOSER_BIN=composer                 # Chemin vers composer
ENV=prod
APP_ENV=prod
APP_DEBUG=0
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

cp .env.prod .env

# -------------------------------
# 3. Mise à jour des permissions AVANT Composer
# -------------------------------
echo "Mise à jour des permissions..."
HTTP_USER=www-data
sudo chown -R $HTTP_USER:$HTTP_USER var/ 2>/dev/null || true
sudo chown -R $HTTP_USER:$HTTP_USER public/ 2>/dev/null || true
sudo chmod -R 775 var/ 2>/dev/null || true

# -------------------------------
# 4. Installer les dépendances Composer (sans exécuter les scripts auto)
# -------------------------------
echo "Installation des dépendances..."
$COMPOSER_BIN install --optimize-autoloader --no-scripts

# -------------------------------
# 5. Installer les assets (si Webpack Encore)
# -------------------------------
if [ -f package.json ]; then
    echo "Installation des assets frontend..."
    /var/www/weddingManager/node_modules/.bin/encore production
fi

# -------------------------------
# 6. Cache et migrations
# -------------------------------
echo "Clear et warmup du cache..."
sudo -u $HTTP_USER $PHP_BIN bin/console cache:clear --env=$ENV 
sudo -u $HTTP_USER $PHP_BIN bin/console cache:warmup --env=$ENV

echo "Exécution des migrations (si nécessaire)..."
sudo -u $HTTP_USER $PHP_BIN bin/console doctrine:migrations:migrate --no-interaction --env=$ENV

# -------------------------------
# 7. Vérification finale des permissions
# -------------------------------
echo "Vérification finale des permissions..."
sudo chown -R $HTTP_USER:$HTTP_USER var/
sudo chown -R $HTTP_USER:$HTTP_USER public/

# -------------------------------
# 8. Redémarrage PHP-FPM
# -------------------------------
echo "Redémarrage de PHP-FPM..."
sudo systemctl reload php8.4-fpm || sudo systemctl restart php8.4fpm



echo "=== Déploiement terminé ==="
