# Architecture des Assets Front-end (Webroot)

Ce répertoire contient tous les fichiers publics (CSS, JS, images).
Pour garantir la maintenabilité, nous appliquons une stricte séparation des responsabilités et une architecture modulaire.

## Arborescence cible

```text
webroot/
├── css/
│   ├── app.css                 <-- Styles globaux
│   └── vendor/                 <-- Librairies CSS externes (si non gérées par un bundler)
├── js/
│   ├── core/                   <-- Scripts fondamentaux (initiation, helpers globaux)
│   │   └── tabulator.factory.js
│   ├── modules/                <-- Logique métier réutilisable (ex: API fetchers)
│   └── views/                  <-- Scripts spécifiques à une page
│       ├── Users/              <-- Nom du Contrôleur (PascalCase)
│       │   ├── index.js        <-- Nom de l'Action (camelCase)
│       │   └── view.js
│       └── Dashboard/
│           └── index.js
```

## Conventions

    1. Mirroir Back/Front : Tout script spécifique à une vue (template CakePHP) doit être placé dans le  sous-dossier views/[NomDuControlleur]/[nom_de_l_action].js.
    2. Pas de logique métier dans les vues JS : Les fichiers sous views/ ne servent qu'à l'instanciation (ex: lier Tabulator à une balise #table). La logique de formatage complexe doit être extraite dans core/ ou modules/.