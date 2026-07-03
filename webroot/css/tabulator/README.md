# Style Core : Tabulator Custom Theme

Ce répertoire est dédié exclusivement aux surcharges graphiques et à la personnalisation visuelle de la librairie Tabulator.js pour l'ensemble de l'application.

## Fichiers
* **custom-theme.css** : Centralise les styles transversaux partagés par toutes les grilles (messages de chargement, placeholders de vacuité, surcharges des en-têtes, etc.).

## Règle d'or (DRY)
Il est strictement interdit d'écrire des styles CSS spécifiques à Tabulator dans la feuille de style globale `app.css`. Toute modification visuelle de l'outil doit être rédigée ici afin de maintenir l'isolation graphique du composant.

## Gestion Spécifique de l'En-tête des Actions (Bypass Popper.js)
Pour intégrer le menu déroulant global ("Créer" / "Réinitialiser") dans l'en-tête de la colonne d'actions sans bug graphique :
1. **Bypass Bootstrap JS** : L'attribut natif `data-bs-toggle="dropdown"` a été **exclu** dans la `ButtonFactory`. Le moteur Popper.js calculait mal les positions absolues au sein du DOM virtuel de Tabulator.
2. **Gestion Manuelle** : Le `TabulatorBuilder` intercepte le clic sur `.action-menu-btn` via son écouteur `headerClick`. Il applique/retire manuellement la classe Bootstrap `.show` sur le menu adjacent (`nextElementSibling`).
3. **Fermeture Contextuelle** : Un écouteur d'événement temporaire est injecté sur le `document` dès l'ouverture pour refermer automatiquement le menu si l'utilisateur clique en dehors de la zone.
4. **Surcharge CSS** : Le fichier `webroot/css/tabulator/custom-theme.css` force la propriété `overflow: visible !important` sur toutes les structures internes de l'en-tête Tabulator pour permettre le débordement visuel du menu.
