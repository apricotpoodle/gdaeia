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
désactivation logique transactionnelle. Il ne déduit pas l'identité de
l'opérateur et conserve la valeur `base` autorisée par le contrôleur. Les
contrôleurs appliquent l'ACL avant de fournir `base=false` par défaut lors
d'une création sans valeur explicite.

Pour un département existant qui est ou devient socle, le service accepte une
bascule de `base` seule. Il retourne des erreurs d'entité si la bascule
accompagne un autre champ ou une mutation de responsable/CGR, avant toute
écriture. Les associations restent gérées par leurs endpoints séparés et
leurs Policies de périmètre.

Il centralise également l'association idempotente et la dissociation immédiate
des responsables et des codes CGR dans les tables de liaison normalisées.

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

Le paramétrage des autorisations utilise désormais `authorization_targets` :
`field_definitions` décrit les champs ORM et ce nouveau référentiel décrit les
zones fonctionnelles (`section`). La table `field_authorizations` conserve le
type et le code de la cible ; une cible inactive reste lisible pour une règle
existante, mais ne peut plus être choisie pour une nouvelle règle.

`UserDepartmentAuthorizationService` contrôle les associations explicites
`user_departments` avant les écritures Web et API. Il compare les états avant
et après hydratation pour couvrir les ajouts comme les retraits et refuse les
départements hors du périmètre de l'opérateur, y compris dans les mutations de
masse.

## Production PDF des DAE

`Pdf/ApplicationformPdfService` génère le document PDF d'une DAE avec mPDF.
Le service reçoit l'identité courante, réutilise la matrice
`FieldAuthorizations` et ne rend que les champs autorisés. Le cycle de
validation est lu depuis ses étapes immuables ; ses statuts sont affichés
avec un libellé et un renforcement par couleur. L'action Web
`/applicationforms/viewpdf/{id}` est protégée par `ApplicationformPolicy` et
retourne le document inline avec un nom `dae-{id}.pdf`.

## Circuits locaux de validation (0017)

`ValidationCircuitAdministrationService` centralise les habilitations, les
références actives et l'atomicité des ajouts groupés ; un doublon refuse le lot.
`Workflow/ValidationCircuitResolver` partage le parcours et ses diagnostics
entre aperçu, enregistrement et lancement : couple département–contrat exact,
rôles parallèles inclus, éligibilité des seules étapes retenues. Le demandeur
est contrôlé au lancement. Les cycles lancés conservent leur instantané.

Voir le [guide opérateur](../../docs/validation-circuit-rules.md) et l'ADR 0070.
