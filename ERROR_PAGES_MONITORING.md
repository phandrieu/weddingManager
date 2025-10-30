# Pages d'erreur personnalisées et Monitoring

## 📋 Vue d'ensemble

Ce projet dispose maintenant de :
1. **Pages d'erreur personnalisées** respectant la charte graphique du site
2. **Système de monitoring automatique** via Microsoft Teams

## 🎨 Pages d'erreur personnalisées

### Emplacement
Les templates se trouvent dans : `templates/bundles/TwigBundle/Exception/`

### Pages disponibles
- **error.html.twig** : Page d'erreur générique (pour toutes les erreurs non spécifiques)
- **error404.html.twig** : Page non trouvée (404)
- **error403.html.twig** : Accès refusé (403)
- **error500.html.twig** : Erreur serveur (500)

### Caractéristiques
- ✅ Respecte la charte graphique du site (couleurs, polices, style)
- ✅ Design responsive (mobile, tablette, desktop)
- ✅ Animations subtiles pour rendre l'erreur moins frustrante
- ✅ Liens de navigation adaptés selon le contexte utilisateur
- ✅ Messages clairs et informatifs
- ✅ Boutons d'action appropriés (retour accueil, page précédente, etc.)

### Test en développement
Pour voir les pages d'erreur en développement, créez temporairement des routes de test ou utilisez :
```bash
php bin/console debug:router
```

En production (avec APP_DEBUG=0), Symfony utilisera automatiquement ces templates.

## 🔔 Monitoring MS Teams

### Architecture
Le système de monitoring envoie automatiquement des notifications vers MS Teams lors d'erreurs critiques.

**Composants :**
- `src/Service/MsTeamsNotificationService.php` : Service d'envoi de notifications
- `src/Monolog/MsTeamsHandler.php` : Handler Monolog personnalisé
- `src/Command/TestTeamsNotificationCommand.php` : Commande de test

### Configuration

#### 1. Configuration MS Teams
Voir le fichier **[TEAMS_SETUP.md](TEAMS_SETUP.md)** pour les instructions détaillées.

En résumé :
1. Créer un canal Teams dédié
2. Ajouter un connecteur "Webhook entrant"
3. Copier l'URL du webhook généré

#### 2. Configuration Application
Ajouter l'URL du webhook dans `.env.local` :
```bash
MS_TEAMS_WEBHOOK_URL="https://outlook.office.com/webhook/VOTRE_URL_ICI"
```

#### 3. Test de la configuration
```bash
php bin/console app:test-teams-notification
```

### Notifications envoyées

**Niveau de gravité :**
- 🚨 EMERGENCY, ALERT, CRITICAL
- ❌ ERROR
- ⚠️ WARNING
- ℹ️ INFO, NOTICE

**Contenu des notifications :**
- Niveau de gravité avec emoji
- Message d'erreur
- Date et heure précises
- Environnement (prod, dev, test)
- URL où l'erreur s'est produite
- Utilisateur connecté (si applicable)
- Adresse IP du client
- User Agent
- Détails de l'exception (type, fichier, ligne, trace complète)

### Filtres automatiques

❌ **NE SONT PAS notifiés :**
- Erreurs 404 (trop fréquentes)
- Erreurs 405
- Events Symfony
- Logs de dépréciation
- Logs de niveau DEBUG

✅ **SONT notifiés :**
- Erreurs ERROR et supérieures
- Exceptions non gérées
- Erreurs critiques d'application

### Sécurité

⚠️ **IMPORTANT** :
- Ne **JAMAIS** commiter l'URL du webhook dans Git
- Utiliser uniquement `.env.local` ou variables d'environnement système
- Restreindre l'accès au canal Teams (canal privé recommandé)
- Régénérer le webhook tous les 6-12 mois

## 🚀 Déploiement en production

### Checklist avant la mise en production

1. **Pages d'erreur**
   - ✅ Les templates sont créés dans `templates/bundles/TwigBundle/Exception/`
   - ✅ Tester en mettant `APP_DEBUG=0` temporairement

2. **Monitoring MS Teams**
   - ✅ Créer le canal Teams et le webhook
   - ✅ Configurer `MS_TEAMS_WEBHOOK_URL` dans `.env.local`
   - ✅ Tester avec `php bin/console app:test-teams-notification`
   - ✅ Vérifier la réception dans Teams

3. **Configuration serveur**
   - ✅ Vider le cache : `php bin/console cache:clear --env=prod`
   - ✅ Vérifier les permissions des fichiers
   - ✅ Configurer les logs : vérifier `config/packages/monolog.yaml`

4. **Tests**
   - Provoquer une erreur volontaire (route inexistante)
   - Vérifier l'affichage de la page d'erreur
   - Vérifier la réception de la notification Teams

### Variables d'environnement requises

```bash
# Production
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=votre_secret_genere

# Monitoring
MS_TEAMS_WEBHOOK_URL=votre_url_webhook_teams
```

## 📊 Monitoring et Maintenance

### Visualiser les logs
```bash
# Logs de production
tail -f var/log/prod.log

# Logs en temps réel
php bin/console server:log
```

### Ajuster la sensibilité des notifications

Éditer `config/packages/monolog.yaml` :

```yaml
# Pour recevoir uniquement les erreurs CRITICAL et supérieures
ms_teams:
    type: service
    id: App\Monolog\MsTeamsHandler
    level: critical  # Au lieu de 'error'
```

### Dépannage

**Aucune notification reçue ?**
1. Vérifier la variable d'environnement : `php bin/console debug:container --env=prod --parameters | grep MS_TEAMS`
2. Tester manuellement : `php bin/console app:test-teams-notification`
3. Vérifier les logs : `tail -f var/log/prod.log`

**Trop de notifications ?**
- Augmenter le niveau minimum (critical au lieu de error)
- Ajouter des canaux à exclure dans monolog.yaml

## 📝 Fichiers créés/modifiés

### Nouveaux fichiers
```
templates/bundles/TwigBundle/Exception/
├── error.html.twig
├── error403.html.twig
├── error404.html.twig
└── error500.html.twig

src/Service/
└── MsTeamsNotificationService.php

src/Monolog/
└── MsTeamsHandler.php

src/Command/
└── TestTeamsNotificationCommand.php

TEAMS_SETUP.md (ce fichier)
ERROR_PAGES_MONITORING.md (ce fichier)
```

### Fichiers modifiés
```
.env (ajout de MS_TEAMS_WEBHOOK_URL)
config/services.yaml (configuration du service MS Teams)
config/packages/monolog.yaml (ajout du handler MS Teams)
```

## 🤝 Support

Pour toute question ou problème :
- Pages d'erreur : Vérifier les templates Twig
- Monitoring : Consulter TEAMS_SETUP.md
- Logs Symfony : https://symfony.com/doc/current/logging.html

---

**Date de mise en place** : 30 octobre 2025
