# 📸 Aperçu des notifications MS Teams

## Exemple 1 : Erreur standard (ERROR)

Quand une exception non gérée se produit, vous recevrez une carte comme celle-ci :

```
┌─────────────────────────────────────────────────────────┐
│ ❌ Erreur détectée sur Notre Messe de Mariage          │
├─────────────────────────────────────────────────────────┤
│ Class 'App\Unknown\Class' not found                     │
│                                                         │
│ 🔔 Niveau: ERROR                                        │
│ ⏰ Date/Heure: 30/10/2025 15:42:18                     │
│ 🌐 Environnement: production                            │
│ 🔗 URL: https://votresite.com/wedding/123/edit        │
│ 👤 Utilisateur: ID: 42                                  │
│ 🌍 IP: 92.184.xxx.xxx                                  │
│                                                         │
│ 🐛 Détails de l'exception                              │
│ Type: Symfony\Component\ErrorHandler\Error\ClassNotFoundError
│ Fichier: /var/www/weddingManager/src/Controller/...   │
│ Ligne: 156                                             │
│ Trace:                                                 │
│ #0 /var/www/.../Controller/WeddingController.php(156) │
│ #1 /var/www/.../vendor/symfony/http-kernel/...        │
│ ...                                                    │
└─────────────────────────────────────────────────────────┘
```

## Exemple 2 : Erreur critique (CRITICAL)

Pour les erreurs plus graves :

```
┌─────────────────────────────────────────────────────────┐
│ 🚨 Erreur détectée sur Notre Messe de Mariage          │
├─────────────────────────────────────────────────────────┤
│ Database connection lost                                │
│                                                         │
│ 🔔 Niveau: CRITICAL                                     │
│ ⏰ Date/Heure: 30/10/2025 16:12:05                     │
│ 🌐 Environnement: production                            │
│ 🔗 URL: https://votresite.com/api/songs               │
│ 👤 Utilisateur: ID: 15                                  │
│ 🌍 IP: 78.234.xxx.xxx                                  │
│                                                         │
│ 🐛 Détails de l'exception                              │
│ Type: Doctrine\DBAL\Exception\ConnectionException      │
│ Fichier: /var/www/weddingManager/vendor/doctrine/...  │
│ ...                                                    │
└─────────────────────────────────────────────────────────┘
```

## Exemple 3 : Warning (WARNING)

Pour les avertissements importants :

```
┌─────────────────────────────────────────────────────────┐
│ ⚠️ Erreur détectée sur Notre Messe de Mariage          │
├─────────────────────────────────────────────────────────┤
│ Memory limit nearly exceeded                            │
│                                                         │
│ 🔔 Niveau: WARNING                                      │
│ ⏰ Date/Heure: 30/10/2025 17:30:22                     │
│ 🌐 Environnement: production                            │
│ 🔗 URL: https://votresite.com/wedding/export          │
│ 👤 Utilisateur: ID: 8                                   │
│ 🌍 IP: 86.192.xxx.xxx                                  │
└─────────────────────────────────────────────────────────┘
```

## Exemple 4 : Notification de test

Lors de l'exécution de `php bin/console app:test-teams-notification` :

```
┌─────────────────────────────────────────────────────────┐
│ ℹ️ Erreur détectée sur Notre Messe de Mariage          │
├─────────────────────────────────────────────────────────┤
│ Test de notification depuis Notre Messe de Mariage      │
│                                                         │
│ 🔔 Niveau: INFO                                         │
│ ⏰ Date/Heure: 30/10/2025 18:00:00                     │
│ 🌐 Environnement: production                            │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ ❌ Erreur détectée sur Notre Messe de Mariage          │
├─────────────────────────────────────────────────────────┤
│ Test d'erreur avec exception                            │
│                                                         │
│ 🔔 Niveau: ERROR                                        │
│ ⏰ Date/Heure: 30/10/2025 18:00:01                     │
│ 🌐 Environnement: production                            │
│ 🔗 URL: https://example.com/test                       │
│ 👤 Utilisateur: TEST_USER                               │
│ 🌍 IP: 127.0.0.1                                       │
│                                                         │
│ 🐛 Détails de l'exception                              │
│ Type: RuntimeException                                 │
│ Ceci est une exception de test pour vérifier le        │
│ format des notifications                               │
│ ...                                                    │
└─────────────────────────────────────────────────────────┘
```

## Couleurs des cartes

Les cartes utilisent des couleurs adaptées selon le niveau :

- 🚨 **EMERGENCY/ALERT/CRITICAL** : Rouge vif (Attention)
- ❌ **ERROR** : Orange (Warning)
- ⚠️ **WARNING** : Jaune (Warning)
- ℹ️ **INFO/NOTICE** : Bleu (Accent)

## Informations toujours présentes

Chaque notification inclut systématiquement :

1. **Niveau de gravité** : Pour trier rapidement les urgences
2. **Date/Heure précise** : Pour identifier quand le problème est survenu
3. **Environnement** : Pour distinguer prod/dev/test
4. **Message d'erreur** : Le message complet et descriptif

## Informations conditionnelles

Selon le contexte, la notification peut aussi inclure :

- **URL** : Si l'erreur provient d'une requête HTTP
- **Utilisateur** : Si un utilisateur était connecté
- **IP** : L'adresse IP du client
- **User Agent** : Le navigateur utilisé
- **Méthode HTTP** : GET, POST, PUT, DELETE, etc.
- **Exception complète** : Type, fichier, ligne, trace

## Avantages des Adaptive Cards

Les notifications utilisent le format **Adaptive Cards** de Microsoft Teams, ce qui permet :

- ✅ Affichage responsive sur mobile et desktop
- ✅ Mise en forme riche (gras, couleurs, icônes)
- ✅ Lisibilité optimale
- ✅ Intégration native dans Teams
- ✅ Possibilité d'ajouter des boutons d'action (future amélioration)

## Actions possibles sur les notifications

Dans Teams, vous pouvez :

- **📌 Épingler** une notification importante
- **💬 Répondre** pour discuter de l'erreur avec l'équipe
- **🔔 Mentionner** un développeur spécifique (@mention)
- **📋 Copier** le contenu pour analyse
- **🔗 Créer un lien** pour référence future

## Filtrage et recherche

Dans Teams, vous pouvez facilement :

- Rechercher par niveau : `ERROR`, `CRITICAL`, etc.
- Rechercher par utilisateur : `ID: 42`
- Rechercher par URL : `wedding/123`
- Filtrer par date/heure

## Statistiques

Grâce aux notifications, vous pouvez facilement :

- Identifier les pages qui génèrent le plus d'erreurs
- Repérer les utilisateurs impactés
- Voir les heures de pic d'erreurs
- Mesurer la stabilité de l'application

---

**Note** : Les exemples ci-dessus sont des représentations textuelles. Les vraies cartes dans MS Teams sont beaucoup plus visuelles et interactives !
