# 0013 — Mutualiser la présentation des erreurs de validation

**Statut :** En cours
**Priorité :** Moyenne

## Contexte

Les contrôleurs Web et API présentent déjà les erreurs de validation en français avec le champ concerné. Leur implémentation reste transitoirement dupliquée.

## Objectif

Mettre en œuvre le service commun `ValidationErrorPresenter` décidé par l'ADR 0052, afin d'uniformiser la présentation des erreurs de validation dans l'ensemble de l'application.

## Session 1 — Inventaire des parcours et contrat retenu

L'inventaire ci-dessous porte sur les échecs de sauvegarde et les réponses visibles par l'utilisateur. Les erreurs d'entité proviennent de `getErrors()` après une validation ou une règle d'intégrité CakePHP.

| Parcours | Ressource | Réponse actuelle en cas d'échec | Réponse attendue | Consommateur |
|---|---|---|---|---|
| Web `Applicationforms::add`, `edit` | `Applicationforms` | Flash avec le premier champ et son motif | Résumé commun dans le Flash ; erreurs CakePHP près des champs | Formulaires Web |
| Web `Users::add`, `edit` | `Users` | Flash avec le premier champ et son motif | Résumé commun dans le Flash ; erreurs CakePHP près des champs | Formulaires Web |
| Web `Users::resetPassword` | `Users` | Flash générique après échec de sauvegarde | Résumé commun si l'entité contient des erreurs ; message générique sinon | Formulaire de réinitialisation |
| Web `Roles::add`, `edit` | `Roles` | Flash « données invalides » | Résumé commun dans le Flash ; erreurs CakePHP près des champs | Formulaires Web |
| Web `Menus::add`, `edit` | `Menus` | Flash générique | Résumé commun si l'entité contient des erreurs ; message générique sinon | Formulaires Web |
| API `Applicationforms::add`, `edit` | `Applicationforms` | HTTP 400, `{success,message}`, première erreur seulement | HTTP 422 et contrat commun | `Applicationforms/create.js`, `edit.js` |
| API `Users::add`, `edit` | `Users` | HTTP 400, `{success,message}`, première erreur seulement | HTTP 422 et contrat commun | `Users/create.js` pour l'ajout ; aucun appel direct à l'édition repéré dans les scripts |
| API `Comments::add`, `edit` | `Comments` | HTTP 400, `{success,message}`, premier motif sans libellé | HTTP 422 et contrat commun | `Applicationforms/applicationform-comments.js` pour l'ajout ; `comments-handler.js` existe, mais aucun chargement n'a été repéré |
| API `FieldAuthorizations::add`, `edit` | `FieldAuthorizations` | HTTP 400, `{success,message}`, premier motif sans libellé | HTTP 422 et contrat commun | Aucun appel de mutation repéré dans les scripts ; les pages Web affichent les formulaires |
| API `Roles::add`, `edit` | `Roles` | HTTP 400, `{success,message,errors}` avec erreurs ORM brutes | HTTP 422 et contrat commun | Aucun appel de mutation repéré dans les scripts ; les formulaires Web ont leurs propres routes |
| API `WorkflowSettings::createCommentTemplate`, `updateCommentTemplate` | `ValidationCommentTemplates` | HTTP 422, `{success,message,errors}` avec erreurs ORM brutes | HTTP 422 et contrat commun | `WorkflowSettings/index.js` |
| API `WorkflowSettings::defaultDueHours` | `WorkflowSettings` | HTTP 400 générique si la sauvegarde échoue | Contrat commun si l'entité contient des erreurs ; échec technique distinct sinon | `WorkflowSettings/index.js` |
| API `Menus` (attribution rôle-menu) | `RoleMenus` | Échec de sauvegarde transformé en exception générique | Contrat commun si la cause est une erreur d'entité ; autres échecs distincts | `Menus/role-access.js` |
| API `Validationsequences` (attribution de rôle) | `Validationsequences` | Échec de sauvegarde transformé en HTTP 400 générique | Contrat commun si la cause est une erreur d'entité ; autres échecs distincts | `Validationsequences/index.js` |

### Classification des échecs

- **Validation d'entité** : échec de sauvegarde avec `getErrors()` non vide, y compris une règle d'unicité ou d'association. Il reçoit le contrat HTTP 422 ci-dessous.
- **Requête mal formée** : paramètre absent ou de type invalide rejeté avant `patchEntity()` ; conserver la réponse de requête invalide et son contrôle existant.
- **Règle métier** : par exemple refus de suppression, séquence non contiguë ou précondition du workflow ; conserver son statut et son message métier. Un HTTP 422 métier existant ne devient pas automatiquement une erreur de champ.
- **Autorisation** : refus par Policy ou contrôle équivalent ; conserver le refus d'accès.
- **Échec technique** : sauvegarde refusée sans erreur d'entité, exception de persistance ou erreur interne ; fournir un message français générique, sans attribuer artificiellement l'échec à un champ ni retourner le contrat de validation.
- **Récupération de mot de passe** : le message volontairement identique pour une adresse connue ou inconnue reste indépendant de la présentation des erreurs d'entité.

### Contrat de validation

Le service `ValidationErrorPresenter` reçoit l'entité et le nom de ressource. Il renvoie un résumé `summary` et une liste `errors` comprenant **tous** les motifs. Le résumé présente la première erreur sous la forme `Champ « {libellé} » : {motif}`. Chaque entrée possède exactement les informations nécessaires à l'interface :

```json
{
  "success": false,
  "message": "Champ « Département » : ce champ est obligatoire.",
  "errors": [
    {"field": "department_id", "label": "Département", "reason": "ce champ est obligatoire."},
    {"field": "jobtitle", "label": "Intitulé du poste", "reason": "ce champ est obligatoire."}
  ]
}
```

L'API renvoie ce JSON avec HTTP 422 uniquement pour une validation d'entité. `field` est le chemin technique complet, avec les indices des associations imbriquées, par exemple `user_departments.0.department_id`. Chaque motif d'un même champ donne une entrée distincte, dans l'ordre fourni par CakePHP. Le libellé français est résolu par ressource ; pour un champ imbriqué, le chemin reste complet et le libellé décrit le champ final. Le Web affiche `summary` dans le Flash et conserve les erreurs natives près des contrôles. Aucun consommateur JavaScript examiné ne lit actuellement `errors` : les scripts utilisent `message` ou le statut HTTP.

### Libellés à centraliser et points de migration

- `Applicationforms` : reprendre les 18 libellés déjà définis en double dans les contrôleurs Web et API : département, créateur, code CGR, type de contrat, motif et précision du motif, imputation budgétaire, intitulé du poste, catégorie professionnelle, temps et répartition du travail, rémunération brute, périodicité, qualification, dates de début et de fin, nom du candidat et champ Oui/Non.
- `Users` : reprendre `email` → « Adresse courriel », `username` → « Nom d'utilisateur », `password` → « Mot de passe », `role_id` → « Rôle applicatif », `user_departments` → « Périmètre organisationnel » et `department_id` → « Département ». Ajouter `firstname` → « Prénom » et `lastname` → « Nom ».
- `Roles` : reprendre les libellés des formulaires (`code` → « Code », `name` → « Libellé », `sort` → « Clé de tri ») et compléter `base` et `deleted` si ces champs produisent des erreurs.
- `Menus` : reprendre les libellés du formulaire pour `parent_id`, `name`, `url`, `active`, `disabled` et `dividor_before` ; couvrir aussi `level` si l'ORM en signale une erreur.
- Créer les libellés manquants pour les champs validés : `Comments` (`model`, `foreign_key`, `type`, `content`, `user_id`, `parent_id`), `FieldAuthorizations` (`role_id`, `resource`, `field`, `access_level`), `ValidationCommentTemplates` (`decision`, `label`, `content`, `position`, `active`), `WorkflowSettings` (`name`, `value`), `RoleMenus` (`role_id`, `menu_id`, `department_id`) et `Validationsequences` (`department_id`, `role_id`, `sequence`, `name`, `description`, `reminder_delay_hours`).
- Traduire à la source les messages de règles d'unicité encore en anglais dans les Tables `FieldAuthorizations`, `RoleMenus` et `Validationsequences`.
- Les scripts de création des demandes et utilisateurs, d'édition des demandes et des paramètres du workflow lisent déjà `message` après un échec HTTP. `Applicationforms/applicationform-comments.js` le lit aussi. `Applicationforms/comments-handler.js` affiche seulement le code HTTP ; si ce script est conservé ou raccordé à une page, lui faire lire `message`. Aucun script examiné ne lit `errors` ni ne teste spécifiquement le statut `400`.

## Session 5 — Vérification des clients et des parcours fonctionnels

Les clients de création et d'édition des demandes, de création des utilisateurs, des commentaires, des paramètres du workflow, des associations rôle-menu et des séquences affichent `message` pour toute réponse HTTP en échec, y compris 422. Aucun de ces clients ne dépend du statut 400 ni de la forme interne de `errors`. Le script conservé `Applicationforms/comments-handler.js`, actuellement non chargé par un template, lit désormais aussi le message JSON avant d'afficher une erreur.

Les tests HTTP couvrent les réponses 422 des routes de création des demandes et utilisateurs, d'ajout et d'édition des commentaires et de création et modification des commentaires prédéfinis. Ils vérifient le message et les champs structurés utiles aux clients. Le test d'édition des commentaires a également révélé un contrôle d'autorisation manuel qui n'était pas signalé au composant CakePHP ; la route applique désormais ce contrôle sans produire d'erreur 500, et un test garantit qu'un autre utilisateur reçoit toujours HTTP 403.

## Critères d'acceptation

- Un unique service est utilisé par les contrôleurs Web et API concernés.
- Chaque message mentionne le libellé français du champ et le motif de l'erreur.
- Les réponses API de validation suivent un contrat homogène et retournent HTTP `422`.
- Des tests automatisés couvrent le service et les réponses API.

## Références

- [ADR 0052 — Présentation unifiée des erreurs de validation Web et API](../adr/0052-presentation-unifiee-erreurs-validation-web-api.md)
