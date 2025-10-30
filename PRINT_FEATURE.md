# Fonctionnalité d'impression du déroulé

## Description

Cette fonctionnalité permet d'imprimer le déroulé de la célébration au format A4, en conservant la mise en forme générale du déroulé visible en édition.

## Utilisation

1. Accédez à la page de visualisation d'un mariage
2. Naviguez jusqu'à l'étape "Répertoire" (étape 3)
3. Cliquez sur le bouton **"Imprimer le déroulé"**
4. La boîte de dialogue d'impression de votre navigateur s'ouvre
5. Vérifiez l'aperçu et lancez l'impression

## Caractéristiques de l'impression

### Format
- **Papier** : A4 portrait
- **Marges** : 1,5 cm (haut/bas) et 1 cm (gauche/droite)

### En-tête
L'impression inclut automatiquement un en-tête avec :
- Titre : "Déroulé de la célébration"
- Noms des mariés
- Date et heure de la célébration
- Lieu (église et paroisse)

### Contenu du déroulé
- **Structure préservée** : Les groupes (Accueil, Liturgie de la Parole, etc.) sont maintenus
- **Types de chants** : Affichés avec badges colorés
- **Choix retenus** : Titre et référence du chant sélectionné
- **Validations** : Boutons MUS (Musicien) et PAR (Paroisse) avec indication visuelle des validations actives
- **Commentaires** : Icône 💬 pour indiquer la présence de commentaires

### Éléments masqués à l'impression
- Navigation et stepper du wizard
- Boutons d'action (Modifier, Retour, etc.)
- Bouton d'ouverture des commentaires
- Modales et éléments interactifs

## Optimisations techniques

### CSS Print Media Queries
Les styles d'impression sont définis dans `assets/styles/app.css` avec :
- `@media print` : règles spécifiques pour l'impression
- `@page` : configuration du format de page A4
- `-webkit-print-color-adjust: exact` : conservation des couleurs lors de l'impression

### Structure du déroulé
- **Évite les coupures** : Les groupes ne sont pas coupés entre deux pages (`page-break-inside: avoid`)
- **Grille adaptée** : Format en 5 colonnes optimisé pour A4
  - Type : 3 cm
  - Choix : 6 cm
  - Validation Musicien : 2 cm
  - Validation Paroisse : 2 cm
  - Commentaires : 1 cm

### Couleurs préservées
Les couleurs importantes sont préservées avec `-webkit-print-color-adjust: exact` :
- En-têtes violets (#652d90)
- Badges de validation verts
- Badges de types

## Fichiers modifiés

### Templates
- `templates/wedding/view.html.twig`
  - Ajout du bouton "Imprimer le déroulé"
  - Ajout de l'en-tête spécial pour l'impression (`.deroule-print-header`)
  - JavaScript pour gérer le clic sur le bouton d'impression

### Styles
- `assets/styles/app.css`
  - Section complète `@media print` (env. 250 lignes)
  - Styles pour `.deroule-print-header` et `.deroule-print-info`
  - Optimisation des colonnes du déroulé pour l'impression

## Conseils pour les utilisateurs

1. **Prévisualiser avant d'imprimer** : Utilisez l'aperçu d'impression pour vérifier le résultat
2. **Couleur ou noir & blanc** : Les couleurs sont préservées mais le document reste lisible en noir et blanc
3. **Économie de papier** : Le format est optimisé pour minimiser le nombre de pages
4. **Export PDF** : Vous pouvez "imprimer" vers un fichier PDF pour partager électroniquement

## Support navigateur

La fonctionnalité d'impression est compatible avec :
- ✅ Chrome/Edge (recommandé pour le meilleur rendu)
- ✅ Firefox
- ✅ Safari
- ⚠️ Les anciens navigateurs peuvent ne pas préserver les couleurs

## Notes techniques

### Classes CSS importantes
- `.d-print-none` : masque l'élément à l'impression
- `.d-none.d-print-block` : affiche l'élément uniquement à l'impression
- `.deroule-print-header` : en-tête spécifique pour l'impression
- `@media print` : toutes les règles d'impression

### JavaScript
Le bouton déclenche `window.print()` après s'être assuré que l'utilisateur est sur l'étape du répertoire.
