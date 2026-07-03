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