# Configuration du Monitoring MS Teams

Ce document explique comment configurer les notifications d'erreurs vers Microsoft Teams pour l'application "Notre Messe de Mariage".

## Vue d'ensemble

Le système de monitoring envoie automatiquement des notifications vers un canal MS Teams lorsque des erreurs se produisent en production. Les notifications incluent :
- Le niveau de l'erreur (Error, Critical, etc.)
- Le message d'erreur
- La date et l'heure
- L'URL où l'erreur s'est produite
- L'utilisateur concerné (si connecté)
- L'adresse IP
- La trace complète de l'exception

## Configuration côté MS Teams (Office 365)

### Prérequis
- Accès administrateur à un tenant Microsoft 365
- Accès à Microsoft Teams
- Droits pour créer/gérer des équipes et des canaux

### Étapes de configuration

#### 1. Créer ou sélectionner une équipe

1. Ouvrez **Microsoft Teams**
2. Dans la barre latérale gauche, cliquez sur **Teams**
3. Créez une nouvelle équipe ou sélectionnez une équipe existante
   - **Recommandation** : Créez une équipe dédiée "Monitoring Production" pour une meilleure organisation

#### 2. Créer un canal dédié

1. Dans l'équipe sélectionnée, cliquez sur les **trois points** (···) à côté du nom de l'équipe
2. Sélectionnez **Ajouter un canal**
3. Nommez le canal : **"Erreurs - Notre Messe de Mariage"** (ou un nom de votre choix)
4. Choisissez la confidentialité :
   - **Standard** : Accessible à tous les membres de l'équipe
   - **Privé** : Accessible uniquement aux membres invités (recommandé pour la sécurité)
5. Cliquez sur **Ajouter**

#### 3. Configurer le connecteur Webhook entrant

1. Dans le canal créé, cliquez sur les **trois points** (···) en haut du canal
2. Sélectionnez **Connecteurs** (ou **Workflows** selon votre version de Teams)

**Pour les versions récentes de Teams (2024+) avec Workflows :**
3. Recherchez **"Webhook"** ou **"Post to a channel when a webhook request is received"**
4. Cliquez sur **Ajouter**
5. Donnez un nom au workflow : **"Monitoring Erreurs Production"**
6. Sélectionnez l'équipe et le canal où poster les notifications
7. Copiez l'**URL du webhook** générée (elle ressemble à : `https://prod-XX.westeurope.logic.azure.com:443/workflows/...`)

**Pour les versions anciennes avec Connecteurs :**
3. Cliquez sur **Configurer** à côté de **"Incoming Webhook"**
4. Donnez un nom au connecteur : **"Monitoring Erreurs Production"**
5. (Optionnel) Téléchargez une icône personnalisée
6. Cliquez sur **Créer**
7. Copiez l'**URL du webhook** générée
8. Cliquez sur **Terminé**

⚠️ **IMPORTANT** : Conservez précieusement cette URL, elle ne sera plus accessible après la fermeture de la fenêtre !

#### 4. Exemple d'URL de webhook

L'URL doit ressembler à :
```
https://outlook.office.com/webhook/XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX@XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX/IncomingWebhook/XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX/XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX
```

Ou pour les workflows :
```
https://prod-XX.westeurope.logic.azure.com:443/workflows/XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX/triggers/manual/paths/invoke?api-version=XXXX&sp=XX&sv=XX&sig=XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
```

## Configuration côté Application

### 1. Ajouter l'URL du webhook dans les variables d'environnement

Éditez votre fichier `.env.local` (ou `.env` pour la production) et ajoutez :

```bash
MS_TEAMS_WEBHOOK_URL="https://outlook.office.com/webhook/VOTRE_URL_COMPLETE_ICI"
```

### 2. Configuration en production

Sur votre serveur de production, définissez la variable d'environnement :

**Option 1 : Fichier .env.local**
```bash
cd /var/www/weddingManager
nano .env.local
```

Ajoutez :
```bash
MS_TEAMS_WEBHOOK_URL="https://outlook.office.com/webhook/VOTRE_URL_COMPLETE_ICI"
```

**Option 2 : Variable d'environnement système**
```bash
export MS_TEAMS_WEBHOOK_URL="https://outlook.office.com/webhook/VOTRE_URL_COMPLETE_ICI"
```

**Option 3 : Dans le virtualhost Apache/Nginx**

Pour Apache, ajoutez dans votre configuration de virtualhost :
```apache
SetEnv MS_TEAMS_WEBHOOK_URL "https://outlook.office.com/webhook/VOTRE_URL_COMPLETE_ICI"
```

Pour Nginx avec PHP-FPM, ajoutez dans votre pool PHP-FPM :
```nginx
env[MS_TEAMS_WEBHOOK_URL] = "https://outlook.office.com/webhook/VOTRE_URL_COMPLETE_ICI"
```

### 3. Vider le cache Symfony

```bash
php bin/console cache:clear --env=prod
```

## Test de la configuration

### Tester l'envoi d'une notification

Vous pouvez tester que la configuration fonctionne avec une commande Symfony personnalisée :

```bash
php bin/console app:test-teams-notification
```

Ou créer un contrôleur de test temporaire :

```php
// src/Controller/TestController.php
use App\Service\MsTeamsNotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    #[Route('/test-teams', name: 'test_teams')]
    public function testTeams(MsTeamsNotificationService $teamsService): Response
    {
        $teamsService->sendErrorNotification(
            'Ceci est un message de test',
            'info',
            ['test' => true]
        );
        
        return new Response('Notification envoyée !');
    }
}
```

⚠️ **N'oubliez pas de supprimer ce contrôleur de test après vérification !**

### Vérification dans Teams

1. Ouvrez Microsoft Teams
2. Accédez au canal configuré
3. Vous devriez voir apparaître une carte avec les informations de l'erreur

## Comportement du système

### Quand les notifications sont envoyées

Les notifications sont envoyées automatiquement pour :
- **Niveau ERROR** : Erreurs applicatives (exceptions non gérées, erreurs PHP, etc.)
- **Niveau CRITICAL** : Erreurs critiques nécessitant une attention immédiate
- **Niveau ALERT** : Situations nécessitant une action immédiate
- **Niveau EMERGENCY** : Le système est inutilisable

### Ce qui N'EST PAS notifié

- Erreurs 404 (Page non trouvée) - trop fréquentes, souvent des bots
- Erreurs 405 (Méthode non autorisée)
- Warnings PHP de niveau faible
- Événements de dépréciation
- Logs de niveau DEBUG, INFO, NOTICE

### Contenu des notifications

Chaque notification inclut :
- 🔔 **Niveau** : Le niveau de gravité de l'erreur
- ⏰ **Date/Heure** : Quand l'erreur s'est produite
- 🌐 **Environnement** : prod, dev, test
- 🔗 **URL** : L'URL où l'erreur s'est produite
- 👤 **Utilisateur** : L'utilisateur connecté (si applicable)
- 🌍 **IP** : L'adresse IP du client
- 🐛 **Exception** : Les détails techniques (type, fichier, ligne, trace)

## Sécurité

### Bonnes pratiques

1. **Ne jamais commiter l'URL du webhook dans Git**
   - Utilisez toujours `.env.local` ou des variables d'environnement
   - L'URL du webhook permet à quiconque d'envoyer des messages dans votre canal

2. **Restreindre l'accès au canal Teams**
   - Utilisez un canal privé
   - N'ajoutez que les personnes nécessaires (équipe technique)

3. **Rotation régulière du webhook**
   - Régénérez le webhook tous les 6-12 mois
   - Changez-le immédiatement si vous suspectez une compromission

4. **Filtrer les données sensibles**
   - Le système ne doit pas envoyer de mots de passe
   - Attention aux tokens dans les URLs ou les contextes

### Révocation d'un webhook compromis

Si l'URL du webhook est compromise :

1. Ouvrez Microsoft Teams
2. Accédez au canal concerné
3. Cliquez sur **··· > Connecteurs** (ou Workflows)
4. Trouvez le webhook existant et **supprimez-le**
5. Créez un nouveau webhook
6. Mettez à jour la variable `MS_TEAMS_WEBHOOK_URL` sur le serveur
7. Videz le cache Symfony

## Dépannage

### Aucune notification ne s'affiche

1. Vérifiez que la variable `MS_TEAMS_WEBHOOK_URL` est bien définie :
   ```bash
   php bin/console debug:container --env=prod --parameters | grep MS_TEAMS
   ```

2. Vérifiez les logs d'application :
   ```bash
   tail -f var/log/prod.log
   ```

3. Testez l'URL du webhook manuellement avec curl :
   ```bash
   curl -H "Content-Type: application/json" -d '{"text":"Test"}' VOTRE_URL_WEBHOOK
   ```

### Les notifications arrivent mais sont mal formatées

- Vérifiez que vous utilisez bien la **version 1.4 d'Adaptive Cards**
- MS Teams peut ne pas supporter certains éléments selon la version

### Trop de notifications

Si vous recevez trop de notifications :

1. Augmentez le niveau minimum dans `config/packages/monolog.yaml` :
   ```yaml
   ms_teams:
       type: service
       id: App\Monolog\MsTeamsHandler
       level: critical  # Au lieu de 'error'
   ```

2. Ajoutez des exclusions dans le handler :
   ```yaml
   ms_teams:
       type: service
       id: App\Monolog\MsTeamsHandler
       level: error
       channels: ["!event", "!deprecation", "!doctrine"]
   ```

## Support

Pour toute question ou problème :
- Consultez la documentation Symfony : https://symfony.com/doc/current/logging.html
- Consultez la documentation MS Teams Webhooks : https://learn.microsoft.com/en-us/microsoftteams/platform/webhooks-and-connectors/how-to/add-incoming-webhook

---

**Dernière mise à jour** : 30 octobre 2025
