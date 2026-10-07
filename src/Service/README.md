# Services applicatifs

## Présentation des erreurs de validation

`ValidationErrorPresenter` transforme les erreurs d'une entité CakePHP en un résumé français et une liste complète d'entrées `{field, label, reason}`. Il reçoit l'entité et son nom de ressource ORM, sans connaître la requête HTTP ni le contrôleur.

Le chemin `field` conserve les associations et leurs indices, par exemple `user_departments.0.department_id`. Les libellés sont centralisés par ressource dans le service. Un champ inconnu reste identifiable avec un libellé de repli.

Les contrôleurs Web utilisent `summary` dans le message Flash via `AppController::validationErrorSummary()`. Les API utilisent `summary` comme `message` et `errors` dans une réponse HTTP 422 via `AppController::validationErrorResponse()` lorsque `getErrors()` contient une erreur de validation. Un échec de sauvegarde sans erreur d'entité reçoit une réponse technique distincte.

Voir le [ticket 0013](../../docs/backlog/0013-mutualiser-la-presentation-des-erreurs-validation.md) et l'[ADR 0052](../../docs/adr/0052-presentation-unifiee-erreurs-validation-web-api.md) pour le contrat complet.

Le référentiel SQL field_definitions est initialisé par migration avec les libellés métier estimés lors de sa création. Il est volontairement en lecture seule dans l’interface ; une correction de libellé doit être portée par une migration ultérieure.

## Production PDF des DAE

`Pdf/ApplicationformPdfService` génère le document PDF d'une DAE avec mPDF.
Le service reçoit l'identité courante, réutilise la matrice
`FieldAuthorizations` et ne rend que les champs autorisés. Le cycle de
validation est lu depuis ses étapes immuables ; ses statuts sont affichés
avec un libellé et un renforcement par couleur. L'action Web
`/applicationforms/viewpdf/{id}` est protégée par `ApplicationformPolicy` et
retourne le document inline avec un nom `dae-{id}.pdf`.
