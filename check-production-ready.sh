#!/bin/bash

# Script de vérification avant mise en production
# Vérifie que tout est en place pour les pages d'erreur et le monitoring MS Teams

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ Vérification avant mise en production"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Couleurs
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

ERRORS=0
WARNINGS=0

# Fonction pour vérifier un fichier
check_file() {
    if [ -f "$1" ]; then
        echo -e "${GREEN}✓${NC} $2"
    else
        echo -e "${RED}✗${NC} $2 - Fichier manquant: $1"
        ((ERRORS++))
    fi
}

# Fonction pour vérifier une variable d'environnement
check_env() {
    if grep -q "^$1=" .env.local 2>/dev/null || grep -q "^$1=" .env 2>/dev/null; then
        VALUE=$(grep "^$1=" .env.local 2>/dev/null || grep "^$1=" .env 2>/dev/null | cut -d '=' -f2-)
        if [ -z "$VALUE" ] || [ "$VALUE" = '""' ] || [ "$VALUE" = "''" ]; then
            echo -e "${YELLOW}⚠${NC} $2 - Variable définie mais vide"
            ((WARNINGS++))
        else
            echo -e "${GREEN}✓${NC} $2"
        fi
    else
        echo -e "${YELLOW}⚠${NC} $2 - Variable non définie"
        ((WARNINGS++))
    fi
}

echo "1️⃣  Vérification des pages d'erreur personnalisées"
echo "─────────────────────────────────────────────────────"
check_file "templates/bundles/TwigBundle/Exception/error.html.twig" "Page d'erreur générique"
check_file "templates/bundles/TwigBundle/Exception/error404.html.twig" "Page d'erreur 404"
check_file "templates/bundles/TwigBundle/Exception/error403.html.twig" "Page d'erreur 403"
check_file "templates/bundles/TwigBundle/Exception/error500.html.twig" "Page d'erreur 500"
echo ""

echo "2️⃣  Vérification du système de monitoring MS Teams"
echo "─────────────────────────────────────────────────────"
check_file "src/Service/MsTeamsNotificationService.php" "Service de notification MS Teams"
check_file "src/Monolog/MsTeamsHandler.php" "Handler Monolog MS Teams"
check_file "src/Command/TestTeamsNotificationCommand.php" "Commande de test"
echo ""

echo "3️⃣  Vérification de la configuration"
echo "─────────────────────────────────────────────────────"
check_env "MS_TEAMS_WEBHOOK_URL" "URL du webhook MS Teams"
check_env "APP_ENV" "Environnement de l'application"
check_env "APP_SECRET" "Secret de l'application"
echo ""

echo "4️⃣  Vérification de la documentation"
echo "─────────────────────────────────────────────────────"
check_file "ERROR_PAGES_MONITORING.md" "Documentation système"
check_file "QUICK_START_PRODUCTION.md" "Guide rapide"
check_file "NOTIFICATION_EXAMPLES.md" "Exemples de notifications"
echo ""

echo "5️⃣  Vérification des services Symfony"
echo "─────────────────────────────────────────────────────"
if php bin/console debug:container App\\Service\\MsTeamsNotificationService --quiet 2>/dev/null; then
    echo -e "${GREEN}✓${NC} Service MsTeamsNotificationService enregistré"
else
    echo -e "${RED}✗${NC} Service MsTeamsNotificationService non enregistré"
    ((ERRORS++))
fi

if php bin/console debug:container App\\Monolog\\MsTeamsHandler --quiet 2>/dev/null; then
    echo -e "${GREEN}✓${NC} Handler MsTeamsHandler enregistré"
else
    echo -e "${RED}✗${NC} Handler MsTeamsHandler non enregistré"
    ((ERRORS++))
fi
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📊 Résumé"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if [ $ERRORS -eq 0 ] && [ $WARNINGS -eq 0 ]; then
    echo -e "${GREEN}✓ Tout est prêt pour la production !${NC}"
    echo ""
    echo "📝 Prochaines étapes :"
    echo "   1. Configurez le webhook MS Teams (voir QUICK_START_PRODUCTION.md)"
    echo "   2. Testez avec : php bin/console app:test-teams-notification"
    echo "   3. Mettez APP_DEBUG=0 en production"
    exit 0
elif [ $ERRORS -eq 0 ]; then
    echo -e "${YELLOW}⚠ $WARNINGS avertissement(s)${NC}"
    echo ""
    echo "⚠️  Avertissements détectés (non bloquants) :"
    echo "   - Configurez MS_TEAMS_WEBHOOK_URL pour activer le monitoring"
    echo "   - Voir QUICK_START_PRODUCTION.md pour les étapes"
    exit 0
else
    echo -e "${RED}✗ $ERRORS erreur(s) et $WARNINGS avertissement(s)${NC}"
    echo ""
    echo "❌ Erreurs critiques détectées !"
    echo "   Certains fichiers sont manquants ou mal configurés."
    echo "   Veuillez corriger les erreurs avant la mise en production."
    exit 1
fi
