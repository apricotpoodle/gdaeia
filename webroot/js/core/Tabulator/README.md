# Module Core : Tabulator Architecture

Ce répertoire contient l'architecture front-end dédiée à la gestion des grilles de données (DataGrids) via la librairie Tabulator.js.

## Principes (DRY & SOLID)
Afin d'éviter la duplication de code (configuration Ajax, pagination distante, gestion des erreurs) dans chaque script de vue, nous utilisons des patrons de conception classiques :

1. **TabulatorBuilder.js (Builder)** : Classe utilitaire chaînable (`return this`) permettant de construire une configuration Tabulator complexe étape par étape de manière sémantique.
2. **TabulatorFactory.js (Factory)** : Classe statique recensant la définition des colonnes et des comportements de chaque entité métier (ex: `createUsersTable`). C'est le seul endroit où la définition des colonnes doit exister.
3. **TabulatorObserver.js (Observer)** : Bus d'événements global (`globalTabulatorObserver`) permettant à différentes grilles d'une même page de communiquer entre elles sans dépendance directe (Pub/Sub).

## Conventions
* Le code métier spécifique à une page (ex: `views/Users/index.js`) ne doit **jamais** instancier `new Tabulator()` directement. Il doit obligatoirement appeler la `TabulatorFactory`.
* Toute fonction ajoutée à ces classes doit impérativement comporter un bloc de documentation `JSDoc` complet.

## Principes du Socle Commun (Tri et Filtrage)
Chaque table générée dans l'application doit proposer de base des fonctionnalités de tri (unitaire ou multi-colonnes) ainsi qu'un système de filtrage en en-tête de colonne. Pour respecter les principes KISS et DRY, ces fonctionnalités sont pilotées via le Monteur (`TabulatorBuilder`).

### Comment Activer/Désactiver globalement ?
Dans votre méthode de Factory, utilisez la méthode `setColumnDefaults()`. Tout paramètre passé à cette méthode est hérité par l'intégralité des colonnes de la grille :

```javascript
// Exemple d'activation complète et immédiate
builder.setColumnDefaults({
    headerSort: true,      // Tri actif partout
    headerFilter: "input"  // Zone de texte de filtrage active partout
});

// Exemple de désactivation complète
builder.setColumnDefaults({
    headerSort: false,
    headerFilter: false
});

```
### Comment gérer une exception sur une colonne précise ?
Les propriétés définies individuellement dans le tableau `setColumns()` ont une priorité absolue et écrasent les configurations globales de `setColumnDefaults()` :

```javascript
builder.setColumns([
    { title: "Nom", field: "lastname" }, // Hérite du tri et du filtre global
    { title: "Actions", field: "id", headerSort: false, headerFilter: false } // Désactivé spécifiquement
]);
```

### Fonctionnement du Mode Distant (Remote)
Lorsque `setRemotePagination()` est invoqué, le comportement passe en mode serveur.

- Tri multiple : Maintenez la touche `Shift` enfoncée lors du clic sur les en-têtes. Tabulator enverra un tableau `sorters` à l'API.

- Filtrage : Saisissez la valeur et appuyez sur `Entrée`. Tabulator transmettra un tableau `filters` contenant le champ, le type d'opérateur et la valeur à l'API CakePHP.

### Configuration de la Colonne d'Actions
La méthode `.setWithActions()` du `TabulatorBuilder` applique par défaut les boutons standards du CRUD de ligne :
* `view` (Read / Bouton Info Bleu)
* `edit` (Update / Bouton Primary Bleu)
* `delete` (Delete / Bouton Danger Rouge)

Le bouton `create` (Create / Bouton Success Vert) est quant à lui inclus de base et géré de manière centralisée dans l'en-tête via le menu engrenage.

**Usage standard (KISS) :**
```javascript
// Génère automatiquement l'en-tête avec Créer/Reset et les lignes avec Voir/Modifier/Supprimer
builder.setWithActions();
```

### Cumuler ou masquer les actions de ligne (CRUD et Spécifiques)
Par défaut, le Monteur pré-configure les boutons CRUD classiques (`view`, `edit`, `delete`). Vous pouvez affiner cette liste grâce au chaînage :

* **Ajouter des actions spécifiques sans toucher au CRUD :**
```javascript
builder.addActions(['impersonate']); // Résultat : view, edit, delete, impersonate
```

### Retirer le CRUD et n'afficher que des actions sur-mesure :
```javascript
builder.setWithActions([]).addActions(['viewpdf']); // Résultat : uniquement le bouton PDF
```

## Routage Automatisé CakePHP (`/{controller}/{action}/{id}`)
Grâce au couplage entre la `ButtonFactory` et le `.setController()` du `TabulatorBuilder`, la navigation est totalement automatisée lors du clic sur un bouton d'action.

### Exemple d'implémentation standard :
```javascript
static createArticlesTable(selector) {
    return new TabulatorBuilder(selector)
        .setAjaxSource("/api/articles.json")
        .setController("articles") // <--- Définit la racine de routage
        .addActions(['viewpdf'])   // <--- Ajoute le bouton PDF (cible '_blank' automatique)
        .build();
}
```

- Clic sur view -> Redirige vers /articles/view/{id} (Même onglet)
- Clic sur edit -> Redirige vers /articles/edit/{id} (Même onglet)
- Clic sur viewpdf -> Ouvre /articles/viewpdf/{id} (Nouvel onglet)

#### Exemple d'implémentation polymorphe (Tableaux mixtes) :
Si vos lignes de données appartiennent à des entités différentes, n'utilisez pas .setController(). Injectez directement le nom du contrôleur cible dans votre JSON d'API sous la clé table_controller :

```JSON
[
  { "id": 12, "title": "Article Un", "table_controller": "articles" },
  { "id": 4, "lastname": "Dupont", "table_controller": "users" }
]
```

Le moteur détectera automatiquement table_controller par ligne et adaptera dynamiquement l'URL finale.

## Gestion Sécurisée Globale (AppEntity Integration)
L'application intègre un contrôle d'accès unifié combinant les droits de ligne (boutons) et de structure (colonnes) :
1. **Boutons d'actions** : Gérés via le dictionnaire `_ui_permissions.actions`. Si une action est évaluée à `false`, le bouton est rendu inerte et transparent via Bootstrap (`disabled`).
2. **Permissions Colonnes** : Gérées via `_ui_permissions.columns`. L'événement global `dataLoaded` intercepte le premier enregistrement et utilise l'API `tableInstance.hideColumn()` pour faire disparaître physiquement et visuellement les colonnes non autorisées du DOM.

### Sécurité Native Intégrée (Gabarit Base)
Le gabarit structurel interne `#getBaseTable` écoute de manière transparente l'événement `dataLoaded`. Tout flux JSON contenant l'arborescence `grid_rights.columns` verra ses colonnes privées masquées dynamiquement par l'infrastructure, sans qu'il ne soit nécessaire de le spécifier dans les fabriques métiers publiques.

### Système de Droits Unifié (`grid_rights`)

Le framework de grille intègre un mécanisme automatique d'application des droits d'accès visuels et structurels :

1. **Permissions sur les Boutons (Lignes)** : Le `formatter` de la colonne d'actions (compilé automatiquement lors du `build()`) intercepte l'objet `grid_rights.actions` de la ligne courante. Si une clé vaut `false`, la `ButtonFactory` injecte l'attribut HTML `disabled="disabled"` et applique les classes d'invalidation de pointeur et d'opacité Bootstrap (`disabled pe-none opacity-25`).
2. **Permissions sur les Colonnes (Structure)** : Gérées au niveau du callback natif `ajaxResponse` dans `TabulatorBuilder.js`. Dès réception du JSON, le premier enregistrement est analysé. L'application des méthodes `hideColumn()` est enveloppée dans un `setTimeout` de 10ms afin de forcer l'exécution du masquage juste après la stabilisation de l'arbre DOM par Tabulator, éliminant tout clignotement ou anomalie graphique.
