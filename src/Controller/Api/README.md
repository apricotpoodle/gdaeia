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
* **`FieldAuthorizationsController`** : Gestion CRUD de la matrice de sécurité des champs et zones (`[role_id, resource, target_type, target, access_level]`). L’administration est réservée aux super-administrateurs ; les niveaux acceptés sont `EDIT`, `VIEW` et `NONE`. `get-form-schema.json` expose les ressources et cibles actives pour les formulaires recherchables.
* **`ValidationsequencesController`** : Administration réservée aux Super Admins des séquences par département.
* **`WorkflowSettingsController`** : Paramétrage global du workflow via `/default-due-hours.json` et `/comment-requirements.json`, ainsi que catalogue paginé des commentaires prédéfinis. Les modèles servent à préremplir le champ de vote ; seul le texte final est conservé dans `validations.obs`.
* **ApplicationformsController** : L'endpoint POST `/applicationforms/{id}/validation/remind.json` relance uniquement les validateurs éligibles aux étapes bloquées du cycle, après contrôle de la Policy administrateur.
* **`DepartmentsController`** : Arbre lazy des départements, fiche, CRUD, calcul d'impact et désactivation logique. Sans filtre, seules les racines sont chargées et les descendants le sont à la demande ; avec filtre, l'arbre complet des branches correspondantes est renvoyé et déployé, avec conservation des ancêtres nécessaires et exclusion des branches sans correspondance. La désactivation accepte un plan de nettoyage explicite réservé au super-administrateur, retourne les opérations effectuées et le bilan des courriels. Les codes CGR sont gérés par des routes imbriquées ; les mutations sont autorisées par `DepartmentPolicy` et `CgrCodePolicy`. La fiche expose aussi les grilles distantes disponibles/associées des responsables et des CGR, avec association ou dissociation immédiate.

Pour les départements, les payloads utilisent `manager_ids` et `cgr_code_ids`.
Le catalogue CGR reste global ; ses associations départementales sont gérées
par les routes imbriquées (`available`, `associated`, `add` et `remove`) et les
mutations passent par `DepartmentManagementService`, après contrôle des
Policies et du périmètre visible des éléments.

Les mutations de champs reçues par les contrôleurs API passent par
`AppController::authorizedRequestData()` avant `patchEntity()`. La matrice
`field_authorizations` est donc appliquée côté serveur en complément des
Policies et des options `accessibleFields` CakePHP.

## Liens ADR
* [ADR 0045 : Administration CRUD de la sécurité des champs](../../docs/adr/0045-gestion-crud-field-authorizations.md)
* [ADR 0046 : Standardisation du CRUD hybride (FieldAuthorizations)](../../docs/adr/0046-standardisation-crud-field-authorizations.md)
* [ADR 0058 : Séparation du paramétrage global du workflow](../../docs/adr/0058-separation-parametrage-global-workflow.md)
* [ADR 0064 : Administration de l'arbre des départements](../../docs/adr/0064-administration-arbre-departements.md)
* [ADR 0065 : ACL automatique des mutations Web et API](../../docs/adr/0065-acl-automatique-des-mutations-web-api.md)

## Administration des circuits locaux

`ValidationCircuitRulesController` expose les règles paginées, leurs diagnostics
`configuration_issues`, l'arbre autorisé, les référentiels actifs et l'aperçu.
Les ajouts groupés, éditions et retraits passent par le service métier et les
Policies serveur. Les habilitations rôle–département sont réservées aux
super-administrateurs ; seule l'identité effective est évaluée sous usurpation.
Les champs de tri/filtre sont limités aux colonnes SQL connues. Les diagnostics
sont calculés sur la page retournée et ne sont ni triables ni filtrables.
Une configuration invalide renvoie HTTP 422 avec `{success,message,errors}`.

Voir le [guide opérateur](../../../docs/validation-circuit-rules.md).
