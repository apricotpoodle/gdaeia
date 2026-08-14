# Module Modèles et Tables (`src/Model/Table`)

Ce répertoire contient la couche d'accès aux données (ORM CakePHP).

## Filtres et Recherches
* **`ApplicationformsTable`** : Intègre le Behavior `FriendsOfCake/Search` avec un filtre callback configuré en `MATCH() AGAINST() IN BOOLEAN MODE` sur l'index FULLTEXT multi-colonnes (`jobtitle`, `applicantname`, `qualification`, `reasonforreplacement`).

## ADRs Associés
* [ADR 0044 : Recherche FULLTEXT MySQL via Callback avec Search](../../docs/adr/0044-filtre-recherche-fulltext-search-plugin.md)