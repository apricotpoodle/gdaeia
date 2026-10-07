# Module API REST (`src/Controller/Api`)

Ce répertoire regroupe les contrôleurs d'API exposant les ressources au format JSON.

## Erreurs de validation

Les mutations refusées avec des erreurs d'entité appellent `AppController::validationErrorResponse()`.
La réponse HTTP 422 contient `{success: false, message, errors}` : `message` décrit le premier
champ et chaque entrée de `errors` contient son chemin technique complet, son libellé français
et son motif. Le service `ValidationErrorPresenter` partage ces libellés avec les formulaires Web.
Une sauvegarde échouée sans erreur ORM reçoit une réponse technique HTTP 500 ; les erreurs de
requête, de droit et de règle métier gardent leurs réponses propres.

## Contrôleurs disponibles

* **`UsersController`** : Exposition paginée des utilisateurs pour Tabulator, création et schéma de champs.
* **`MenusController`** : Distribution de l'arborescence des menus filtrée par rôles.
* **`FieldAuthorizationsController`** : Gestion CRUD de la matrice de sécurité des champs (`[role_id, resource, field, access_level]`). L’administration est réservée aux super-administrateurs ; les niveaux acceptés sont `EDIT`, `VIEW` et `NONE`. Les pages Web servent les formulaires et les mutations passent par les endpoints JSON `add`/`edit`, avec une suppression hybride compatible Tabulator.
* **`ValidationsequencesController`** : Administration réservée aux Super Admins des séquences par département.
* **`WorkflowSettingsController`** : Paramétrage global du workflow via `/default-due-hours.json` et `/comment-requirements.json`, ainsi que catalogue paginé des commentaires prédéfinis. Les modèles servent à préremplir le champ de vote ; seul le texte final est conservé dans `validations.obs`.

## Liens ADR
* [ADR 0045 : Administration CRUD de la sécurité des champs](../../docs/adr/0045-gestion-crud-field-authorizations.md)
* [ADR 0046 : Standardisation du CRUD hybride (FieldAuthorizations)](../../docs/adr/0046-standardisation-crud-field-authorizations.md)
* [ADR 0058 : Séparation du paramétrage global du workflow](../../docs/adr/0058-separation-parametrage-global-workflow.md)
