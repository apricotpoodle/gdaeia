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

## Cinématique d'Interception des Événements et Routage Dynamique

L'architecture orchestre les interactions utilisateurs (clics sur les lignes et pressions sur les boutons d'actions) à travers quatre couches hermétiques afin de garantir la performance, l'étanchéité de la sécurité graphique et un découplage total (DRY/SOLID).

### 1. Couche DOM Natif : Isolation du Bouillonnement (Event Bubbling)
Afin d'éviter qu'un clic sur un bouton CRUD situé dans une cellule ne déclenche accidentellement l'événement de sélection de la ligne entière, la colonne d'actions intercepte l'événement au plus tôt dans son gestionnaire `cellClick`.
L'instruction systématique `e.stopPropagation()` coupe net la remontée de l'événement dans l'arbre DOM du navigateur. Le clic sur un bouton exécute l'action associée, tandis que le clic n'importe où ailleurs sur la ligne déclenche le comportement de sélection de l'enregistrement.

### 2. Couche Noyau (TabulatorBuilder) : Routage Universel et Calcul d'URL
Le moteur `TabulatorBuilder.js` centralise l'intelligence d'aiguillage au sein de la méthode privée `_compileActionColumn()` :
* **Calcul d'URL Industriel** : Avant toute évaluation du mode d'action, le Builder assemble dynamiquement l'URL cible conventionnelle de CakePHP sous la forme `/${contrôleur}/${action}/${id}` en combinant le contexte de la table et les métadonnées de la ligne.
* **Le Contrat `_actionUrl`** : Si un bouton est configuré comme un événement d'interaction locale (`isEvent: true` dans le registre de la `ButtonFactory`), le Builder annule la redirection matérielle du navigateur et injecte l'URL pré-calculée directement dans l'objet de données de la ligne sous la propriété masquée `_actionUrl`. Il transmet ensuite ce colis complet au bus de communication.

### 3. Couche Distribution (TabulatorObserver) : Sémantique Unifiée
La transmission des signaux vers l'extérieur de la grille s'appuie sur le patron de conception **Publish-Subscribe (Pub/Sub)** géré par le `globalTabulatorObserver`. Les canaux d'émissions sont normalisés pour utiliser rigoureusement le sélecteur CSS d'en-tête (incluant le caractère `#`), éliminant toute dérive sémantique (CamelCase ou chaînes orphelines) :
* Clic sur une ligne : `${this.selector}:rowClick` (Ex: `#users-table:rowClick`)
* Clic sur un bouton événement : `${this.selector}:action:${action}` (Ex: `#users-table:action:edit`)

### 4. Couche Applicative (Orchestrateur de Vue) : Consommation Passive
Le script d'orchestration de la page (ex: `views/Users/index.js`) est le seul récepteur des événements de l'Observer. Grâce à l'injection de l'infrastructure, la vue ne contient **aucune URL codée en dur (hardcoded)** :
1. Elle intercepte le signal transmis par le canal unifié.
2. Elle prend en charge l'expérience utilisateur synchrone (ex: boîtes de dialogue `confirm()` ou ouverture de modales graphiques Bootstrap).
3. Si l'action est validée par l'utilisateur, elle consomme directement la propriété `user._actionUrl` fournie par le colis technique pour exécuter la redirection ou l'appel distant.

### 5. Intégration Asynchrone (Appels API & UX)
L'orchestrateur de vue est configuré pour consommer l'URL d'action générée de manière asynchrone (`fetch`) sur les événements critiques (ex: `delete`).
En cas de succès, la vue exécute instantanément `tableInstance.deleteRow(id)` sans recharger la page, et notifie l'utilisateur via le `FlashManager` de l'infrastructure `Core`, garantissant une fluidité de type Single Page Application (SPA). En cas d'échec serveur, l'exception métier JSON de CakePHP est interceptée et restituée visuellement.

### 6. Isolation et Contrôle Impératif des Menus d'En-tête (Dropdowns)
L'activation de la persistance d'état globale (`enableStatePersistence`) provoque un re-rendu (re-render) structurel lourd de l'en-tête par le moteur de Tabulator lors de l'hydratation des filtres. Durant cette phase, Tabulator purge les attributs HTML non reconnus par son schéma natif, rendant l'initialisation automatique de Bootstrap 5 (`data-bs-toggle="dropdown"`) totalement inérante.

Pour contourner cette régression sans créer de régression visuelle :
* **Dépendances épurées** : Le balisage généré par la `ButtonFactory.getHeaderDropdown()` est maintenu en HTML/CSS standard, sans attribut de ciblage tiers.
* **Contrôle Programmatique** : La méthode `headerClick` du `TabulatorBuilder` prend le contrôle absolu de la visibilité en manipulant directement les propriétés CSS de rendu (`display: block/none`) et la classe `show`. Un écouteur temporaire est greffé sur le `document` lors de l'ouverture pour intercepter les clics extérieurs (Outside Click) et garantir la fermeture propre du menu, préservant une expérience utilisateur fluide sans conflit de cycle de vie.

### 7. Habilitations de l'En-tête (Gouvernance & Découplage)
Pour éviter le "Risque de la Table Vide" (où une table contenant 0 enregistrement empêcherait la lecture des droits applicatifs), le droit de création global (`Create`) est strictement dissocié du payload de données JSON.

* **Cinématique** : Le serveur (Back-End) injecte la permission au niveau du conteneur DOM via l'attribut `data-can-create="true|false"`.
* **Avantage** : Le `TabulatorBuilder` évalue cette propriété de manière synchrone lors de la méthode `_compileActionColumn()`. Le menu d'actions est configuré à sa juste valeur dès le premier rendu, garantissant une étanchéité parfaite et éliminant tout clignotement d'interface ou dépendance à la présence de lignes de données.
* **Action 'Reset'** : Arbitrairement définie comme **toujours accessible**. Cette action étant purement locale (Front-End) et dédiée au nettoyage du `localStorage`, sa restriction impacterait négativement l'expérience utilisateur sans apporter de gain de sécurité sur le serveur.

### 8. Comportement Multi-Grilles sur une même Page
L'architecture supporte nativement la coexistence de plusieurs grilles au sein d'une même vue.
Chaque grille est instanciée via le Helper `<?= $this->Tabulator->renderGrid('#id', 'Controleur') ?>`.
Le noyau calcule une empreinte de stockage local distincte pour chaque sélecteur (`${URL}-${Sélecteur}`) et lit l'attribut `data-can-create` localisé sur son propre conteneur. Le bus de communication `TabulatorObserver` distribue les signaux de manière étanche en préfixant les canaux par le sélecteur CSS unique de la grille émettrice.

### 9. Sécurisation des Endpoints d'API de Grilles
Chaque contrôleur d'API (ex: `src/Controller/Api/UsersController.php`) exposant des données au format JSON pour Tabulator est soumis aux mêmes exigences de sécurité strictes que les contrôleurs HTML. L'action `index()` doit idéalement invoquer `$this->Authorization->authorize($this->Table->newEmptyEntity(), 'index')` dès son démarrage afin de lever le verrou du middleware global d’Authorization. Cela assure une validation étanche des privilèges de lecture de l'utilisateur avant l'exécution des requêtes de pagination et de tri de l'adaptateur.

### 10. Partage de Session et Requêtes Cross-Origin (AJAX Credentials)
Pour éviter que les requêtes de données asynchrones de Tabulator ne soient rejetées comme anonymes par le middleware d'authentification (ce qui couperait court au cycle de vie et provoquerait une erreur 500 d'autorisation manquante), la configuration `ajaxConfig` du `TabulatorBuilder` doit être configurée avec `credentials: "include"`. Cette propriété force la transmission systématique des cookies de session du navigateur lors de chaque appel d'API sous-jacent.

### 11. Résolution des conflits de RequestHandler JSON et Authorization
Lors du traitement des requêtes d'API se terminant par `.json`, le `RequestHandlerComponent` de CakePHP initie un cycle de rendu précoce. Pour éviter que le middleware d'Authorization ne bloque le flux avec une exception 500 (contrôle manquant), l'exemption d'autorisation doit être déclarée de manière synchrone dans le hook `beforeFilter()` du contrôleur d'API concerné, garantissant l'étanchéité du pipeline de sérialisation.

### 12. Intégrité de la Mémoire et Itérateurs (ResultSet)
Lors de l'injection des `grid_rights` par le `TabulatorAdapter`, il ne faut jamais itérer directement sur l'objet retourné par `$this->paginate()` si l'on compte le transmettre tel quel au moteur JSON. Cet objet est un `ResultSet` (Itérateur). Si l'on modifie ses entités à la volée puis qu'on le donne au sérialiseur JSON, CakePHP risque de relancer la requête (ré-itération) et de purger les modifications faites en mémoire.
**Règle :** Toujours extraire et figer les entités dans un tableau physique (Array) via une boucle `foreach` ou `toList()` avant d'y injecter des propriétés dynamiques.

### 13. Pagination CakePHP 5 et perte de la variable `last_page`
Dans CakePHP 5, l'objet retourné par `paginate()` implémente `PaginatedInterface`. Les métadonnées ne sont plus stockées sous forme de tableau associatif magique, ce qui provoque la disparition de la clé `last_page` attendue par Tabulator.
L'adaptateur doit manuellement extraire cette donnée via la méthode `$paginatedData->pagingParams()` et restructurer le flux JSON de sortie :
```php
return [
    'data' => $entitiesFigees,
    'last_page' => $pagingParams['pageCount'] ?? 1
];
