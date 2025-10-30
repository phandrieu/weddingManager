# 🚀 Guide Rapide - Mise en Production

## ✅ Ce qui a été fait

### 1. Pages d'erreur personnalisées
- ✅ Page 404 (non trouvée) avec navigation contextuelle
- ✅ Page 403 (accès refusé) avec redirection connexion
- ✅ Page 500 (erreur serveur) avec message rassurant
- ✅ Page d'erreur générique
- ✅ Design respectant la charte graphique
- ✅ Responsive (mobile, tablette, desktop)

### 2. Système de monitoring MS Teams
- ✅ Service d'envoi de notifications vers MS Teams
- ✅ Handler Monolog personnalisé
- ✅ Configuration automatique pour l'environnement de production
- ✅ Commande de test
- ✅ Documentation complète

## 📋 Ce que VOUS devez faire sur MS Teams

### Étape 1 : Créer un canal Teams (5 minutes)

1. Ouvrez **Microsoft Teams**
2. Dans votre équipe (ou créez-en une nouvelle) :
   - Cliquez sur les **trois points** (···) à côté du nom de l'équipe
   - **Ajouter un canal**
   - Nom : `Erreurs - Notre Messe de Mariage`
   - Confidentialité : **Privé** (recommandé)
   - Cliquez sur **Ajouter**

### Étape 2 : Créer le Webhook (3 minutes)

**Option A - Teams moderne (2024+) avec Workflows :**
1. Dans le canal créé, cliquez sur **les trois points** (···) en haut
2. Cherchez **Workflows** ou **Connecteurs**
3. Recherchez **"Webhook"** ou **"Post to a channel when a webhook request is received"**
4. Cliquez sur **Ajouter**
5. Nom du workflow : `Monitoring Production`
6. **Copiez l'URL générée** (elle commence par `https://prod-XX.westeurope.logic.azure.com...`)

**Option B - Teams classique avec Connecteurs :**
1. Dans le canal créé, cliquez sur **les trois points** (···) en haut
2. Sélectionnez **Connecteurs**
3. Cherchez **"Incoming Webhook"** (ou "Webhook entrant")
4. Cliquez sur **Configurer**
5. Nom : `Monitoring Production`
6. Ajoutez une icône (optionnel)
7. Cliquez sur **Créer**
8. **Copiez l'URL générée** (elle commence par `https://outlook.office.com/webhook...`)

⚠️ **IMPORTANT** : Sauvegardez cette URL immédiatement, vous ne pourrez plus la récupérer !

### Étape 3 : Configurer l'application (2 minutes)

1. Sur votre serveur, éditez le fichier `.env.local` :
   ```bash
   nano /var/www/weddingManager/.env.local
   ```

2. Ajoutez cette ligne avec l'URL copiée à l'étape 2 :
   ```bash
   MS_TEAMS_WEBHOOK_URL="https://outlook.office.com/webhook/VOTRE_URL_COMPLETE_ICI"
   ```
   Ou si vous utilisez Workflows :
   ```bash
   MS_TEAMS_WEBHOOK_URL="https://prod-XX.westeurope.logic.azure.com/workflows/..."
   ```

3. Sauvegardez et quittez (Ctrl+X, puis Y, puis Entrée)

4. Videz le cache :
   ```bash
   cd /var/www/weddingManager
   php bin/console cache:clear --env=prod
   ```

### Étape 4 : Tester la configuration (1 minute)

Exécutez la commande de test :
```bash
php bin/console app:test-teams-notification
```

Vous devriez voir :
- ✅ "Notification de test envoyée avec succès !"
- ✅ Une carte apparaître dans votre canal Teams

## 🎯 Résultat attendu

Une fois configuré, vous recevrez automatiquement dans Teams :
- 🚨 Les erreurs critiques (CRITICAL, EMERGENCY)
- ❌ Les erreurs standards (ERROR)
- ⚠️ Les warnings importants

**Chaque notification contient :**
- Le niveau d'erreur avec emoji
- Le message d'erreur complet
- La date et l'heure exacte
- L'URL où l'erreur s'est produite
- L'utilisateur concerné (si connecté)
- L'adresse IP du visiteur
- La trace complète de l'exception

## 🔒 Sécurité - TRÈS IMPORTANT

❌ **NE JAMAIS** :
- Commiter l'URL du webhook dans Git
- Partager l'URL du webhook publiquement
- Utiliser le même webhook pour plusieurs applications

✅ **TOUJOURS** :
- Utiliser `.env.local` (non commité dans Git)
- Restreindre l'accès au canal Teams (privé)
- Régénérer le webhook tous les 6-12 mois
- Changer immédiatement si compromis

## 📚 Documentation détaillée

Pour plus d'informations :
- **[TEAMS_SETUP.md](TEAMS_SETUP.md)** : Documentation complète MS Teams
- **[ERROR_PAGES_MONITORING.md](ERROR_PAGES_MONITORING.md)** : Vue d'ensemble du système

## 🆘 En cas de problème

### Le webhook ne fonctionne pas ?
```bash
# Vérifier que la variable est bien définie
php bin/console debug:container --env=prod --parameters | grep MS_TEAMS

# Tester manuellement avec curl
curl -H "Content-Type: application/json" \
     -d '{"text":"Test"}' \
     VOTRE_URL_WEBHOOK
```

### Trop de notifications ?
Éditez `config/packages/monolog.yaml` et changez le niveau :
```yaml
ms_teams:
    level: critical  # Au lieu de 'error'
```

### Besoin d'aide ?
- Consultez les logs : `tail -f var/log/prod.log`
- Relancez le test : `php bin/console app:test-teams-notification`
- Vérifiez la documentation MS Teams : [Documentation officielle](https://learn.microsoft.com/en-us/microsoftteams/platform/webhooks-and-connectors/how-to/add-incoming-webhook)

---

**Temps total estimé de configuration : 10-15 minutes**

Bonne mise en production ! 🎉
