# Module API REST (`src/Controller/Api`)

Ce répertoire regroupe les contrôleurs d'API exposant les ressources au format JSON.

## Contrôleurs disponibles

* **`UsersController`** : Exposition paginée des utilisateurs pour Tabulator, création et schéma de champs.
* **`MenusController`** : Distribution de l'arborescence des menus filtrée par rôles.
* **`FieldAuthorizationsController`** : Gestion CRUD de la matrice de sécurité des champs (`[role_id, resource, field, access_level]`).

## Liens ADR
* [ADR 0045 : Administration CRUD de la sécurité des champs](../../docs/adr/0045-gestion-crud-field-authorizations.md)
* [ADR 0046 : Standardisation du CRUD hybride (FieldAuthorizations)](../../docs/adr/0046-standardisation-crud-field-authorizations.md)