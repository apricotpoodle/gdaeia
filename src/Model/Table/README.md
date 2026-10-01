# Module Modèles et Tables (`src/Model/Table`)

Ce répertoire contient la couche d'accès aux données (ORM CakePHP).

## Filtres et Recherches
* **`ApplicationformsTable`** : Intègre le Behavior `FriendsOfCake/Search` avec un filtre callback configuré en `MATCH() AGAINST() IN BOOLEAN MODE` sur l'index FULLTEXT multi-colonnes (`jobtitle`, `applicantname`, `qualification`, `reasonforreplacement`).
* **`WorkflowSettingsTable`** et **`ValidationCommentTemplatesTable`** : Portent respectivement les délais globaux et le catalogue administrable des commentaires de validation.

## Erreurs de validation

Les Tables restent la source des règles de validation et d'intégrité. Leurs messages métier, notamment les refus de doublons pour les autorisations de champs, les associations rôle-menu, les séquences de validation et les départements d'un utilisateur, sont rédigés en français. `ValidationErrorPresenter` présente ces erreurs sans les définir à la place des Tables.

## ADRs Associés
* [ADR 0044 : Recherche FULLTEXT MySQL via Callback avec Search](../../docs/adr/0044-filtre-recherche-fulltext-search-plugin.md)
* [ADR 0052 : Présentation unifiée des erreurs de validation Web et API](../../docs/adr/0052-presentation-unifiee-erreurs-validation-web-api.md)
