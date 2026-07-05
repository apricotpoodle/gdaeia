# ADR 0026 : Contrôle d'Accès Visuel et Structurel Unifié des Grilles via 'grid_rights'

**Date :** 04 Juillet 2026
**Statut :** Accepté

## Contexte
Avec la mise en place de la `ButtonFactory` (ADR 0023) et de la standardisation des fonctionnalités (ADR 0014), nos grilles applicatives gèrent plus de 30 actions métiers. Pour des impératifs stricts de sécurité d'entreprise, l'affichage des composants d'interface (verrouillage des boutons d'actions par ligne et masquage des colonnes entières contenant des données sensibles comme `issuperuser`) doit être piloté exclusivement par le serveur (Back-End).

Cependant, les cycles de traitement asynchrones et l'hydratation distante (Remote) de Tabulator provoquent des anomalies de synchronisation graphique : l'écouteur `dataProcessed` se déclenche trop tôt, avant la stabilisation géométrique du DOM, ce qui empêche l'application des méthodes structurelles comme `hideColumn()`. De plus, les rétentions agressives de mémoire tampon (cache) du navigateur paralysent le déploiement des scripts d'infrastructure JavaScript en local et en production.

## Décision
1. **Contrat d'Échange Centralisé (AppEntity)** : Toutes les entités CakePHP destinées à être rendue dans une grille héritent d'une classe parente abstraite `AppEntity`. Celle-ci injecte au payload JSON une propriété virtuelle unique et protégée nommée `grid_rights`.
2. **Architecture du Payload Sécurisé** : La clé `grid_rights` sépare hermétiquement les périmètres d'exécution graphiques :
   - `actions` (Dictionnaire de booléens par ligne) : Gère l'activation ou la désactivation des boutons CRUD individuels.
   - `columns` (Dictionnaire de booléens par table) : Détermine si une colonne doit être structurellement présente ou masquée du DOM.
3. **Interception Synchrone Immuable (TabulatorBuilder)** : La logique de traitement des droits de colonnes est retirée des fabriques de grilles métiers et s'intègre de manière obligatoire au cœur de la méthode `build()` du `TabulatorBuilder` via le callback natif `ajaxResponse`.
4. **Contournement Macro-Task du Timing DOM** : L'exécution des boucles d'ordres `tableInstance.hideColumn()` au sein de `ajaxResponse` est encapsulée dans un micro-différé asynchrone (`setTimeout` de 10ms) afin de forcer l'ajustement géométrique juste après la création physique des nœuds DOM par Tabulator.
5. **Cache-Busting Applicatif Permanent** : Modification de la configuration des assets de CakePHP (`config/app.php`) pour forcer le timestamping des fichiers statiques.

## Spécification de la structure de données (grid_rights)
Le payload JSON transmis au composant front-end respecte le format d'imbrication strict suivant :
```json
"grid_rights": {
    "actions": {
        "view": true,
        "edit": false,
        "delete": false
    },
    "columns": {
        "email": true,
        "issuperuser": false
    }
}
```

## Justification
    1. Respect des Principes DRY et KISS : Centraliser l'écoute des droits dans le cœur du TabulatorBuilder évite de dupliquer et de copier-coller les écouteurs d'événements (dataLoaded ou renderComplete) dans les 30+ fabriques de grilles de l'application.

    2. Expérience Utilisateur Stable (Zéro Clignotement) : Le recours au callback ajaxResponse couplé au différé de 10ms offre la garantie mathématique que la table possède ses données avant d'altérer sa structure, éliminant ainsi les décalages de colonnes visuels et les clignotements d'en-têtes à l'écran.

    3. Pérennité du Versioning des Déploiements : Forcer la configuration Asset.timestamp à la valeur 'force' ordonne à CakePHP de générer un paramètre de requête (ex: ?v=1783176245) basé uniquement sur la date de modification réelle du fichier sur le disque. Le navigateur conserve le cache de manière performante, mais le brise instantanément à chaque mise à jour de fichier d'infrastructure.

## Conséquences
    - Sécurité garantie par le serveur : L'interface applique aveuglément les directives calculées par les Policies et le modèle PHP ; modifier le DOM localement ne permet pas de contourner les droits.

    - Allègement drastique des Factories métiers : La déclaration des tables spécifiques (ex: createUsersTable) se focalise uniquement sur les données métiers brutes, l'infrastructure prenant en charge l'évaluation structurelle automatique de sécurité.

    - Maintenance simplifiée : Toute modification de droits applicatifs sur un profil s'effectue dans le modèle PHP sans nécessiter de refonte ou de mise à jour des scripts front-end.


## ⚠️ Piège Architectural : L'écrasement par la Sérialisation JSON (Propriétés Virtuelles)

### Le Problème (Le Fantôme de l'Entité)
Lors du développement initial, il est tentant de déclarer la propriété `grid_rights` dans le tableau `$_virtual` de l'entité de base (`AppEntity.php`) avec un accesseur statique `_getGridRights()` renvoyant des permissions bouchonnées (Mocks).

Cependant, cette approche détruit la logique dynamique générée par l'adaptateur. En effet :
1. Le `TabulatorAdapter` calcule les vrais droits via la `UserPolicy` et les attache à l'entité en mémoire (`$entity->grid_rights = [...]`).
2. Lors du rendu, la méthode `$this->set()` déclenche le moteur de sérialisation JSON de CakePHP.
3. Le moteur détecte la propriété dans `$_virtual`, invoque automatiquement `_getGridRights()`, et **écrase brutalement** les droits dynamiques calculés par l'adaptateur avec les valeurs figées de l'entité mère.

### La Solution (Responsabilité Unique)
Les entités du modèle (Data Objects) doivent rester strictement agnostiques des logiques de présentation UI.
- Ne **jamais** déclarer `grid_rights` dans `$_virtual`.
- Ne **jamais** coder de méthode `_getGridRights()` dans le modèle.
- Laisser l'adaptateur (`TabulatorAdapter`) injecter dynamiquement la propriété `$entity->grid_rights`. Le sérialiseur JSON natif de CakePHP inclura parfaitement cette nouvelle propriété publique ajoutée à la volée, garantissant que les droits reflètent la réalité de la `Policy`.
