# Services applicatifs

## Présentation des erreurs de validation

`ValidationErrorPresenter` transforme les erreurs d'une entité CakePHP en un résumé français et une liste complète d'entrées `{field, label, reason}`. Il reçoit l'entité et son nom de ressource ORM, sans connaître la requête HTTP ni le contrôleur.

Le chemin `field` conserve les associations et leurs indices, par exemple `user_departments.0.department_id`. Les libellés sont centralisés par ressource dans le service. Un champ inconnu reste identifiable avec un libellé de repli.

Les contrôleurs Web utilisent `summary` dans le message Flash via `AppController::validationErrorSummary()`. Les API utilisent `summary` comme `message` et `errors` dans une réponse HTTP 422 via `AppController::validationErrorResponse()` lorsque `getErrors()` contient une erreur de validation. Un échec de sauvegarde sans erreur d'entité reçoit une réponse technique distincte.

Voir le [ticket 0013](../../docs/backlog/0013-mutualiser-la-presentation-des-erreurs-validation.md) et l'[ADR 0052](../../docs/adr/0052-presentation-unifiee-erreurs-validation-web-api.md) pour le contrat complet.

Le référentiel SQL field_definitions est initialisé par migration avec les libellés métier estimés lors de sa création. Il est volontairement en lecture seule dans l’interface ; une correction de libellé doit être portée par une migration ultérieure.

## Gestion des départements

`DepartmentManagementService` centralise les règles de l'administration des
départements : chargement des enfants actifs, prévention des cycles, calcul des
impacts, blocage des déplacements ou changements de stratégie incompatibles et
désactivation logique transactionnelle. Le service force les nouveaux
départements administrés à ne pas être des lignes socle.

`deactivateWithCleanup()` produit un plan vérifiable avant exécution : les
formulaires actifs et les codes CGR système restent bloquants ; les autres
catégories doivent être confirmées explicitement par le super-administrateur.
Le nettoyage détache les utilisateurs sans désactiver leurs comptes, désactive
les séquences et codes CGR non système, puis désactive le département (ou son
sous-arbre) dans une transaction unique.

## ACL des mutations Web et API

`FieldAuthorizationService` construit le schéma de droits à partir de
`field_authorizations` et filtre les données reçues avant `patchEntity()`.
Les contrôleurs Web et API utilisent les helpers communs d’`AppController`.
Une ressource possédant une matrice active refuse par défaut les champs
absents ; un super-administrateur conserve l’accès complet. Les commandes CLI,
imports et workflows internes restent hors du périmètre HTTP et appliquent
leurs propres listes blanches.

Voir l’[ADR 0065](../docs/adr/0065-acl-automatique-des-mutations-web-api.md).

## Production PDF des DAE

`Pdf/ApplicationformPdfService` génère le document PDF d'une DAE avec mPDF.
Le service reçoit l'identité courante, réutilise la matrice
`FieldAuthorizations` et ne rend que les champs autorisés. Le cycle de
validation est lu depuis ses étapes immuables ; ses statuts sont affichés
avec un libellé et un renforcement par couleur. L'action Web
`/applicationforms/viewpdf/{id}` est protégée par `ApplicationformPolicy` et
retourne le document inline avec un nom `dae-{id}.pdf`.
