# ADR 0075 — Protection des références socles

**Date :** 13 novembre 2026
**Statut :** Proposé
**Dépendances :** ADR 0005, ADR 0042, ADR 0045, ADR 0046, ADR 0052 et ADR 0065

## Contexte

Neuf référentiels exposent `base`, `code`, `name` et `sort`. Les Policies
protègent leurs parcours HTTP, mais une écriture ORM interne peut contourner
ces parcours. Les codes de certaines références participent aux règles métier.
Un contrat commun est nécessaire sans assimiler toutes les Tables portant ces
colonnes à des références socles.

## Décision proposée

`ProtectBaseRecordsBehavior` applique le contrat aux opérations ORM
standard. Les sept nomenclatures communes héritent de
`ReferenceTable extends AppTable` ; `DepartmentsTable` et `RolesTable`
attachent le Behavior explicitement et conservent leurs architectures propres.
`AppTable` ne déduit aucune règle de la présence de colonnes.

`id` est immuable pour toutes les lignes. La création d'une ligne avec
`base = true` et ses autres valeurs initiales est permise. Lors d'une mise à
jour, si la valeur persistée ou demandée de `base` est vraie, le seul
changement permis est celui de `base` lui-même. Les basculements dans les
deux sens sont donc isolés. `code` ne peut pas changer sur une ligne socle
ni lors de son activation. Une ligne qui demeure socle ne peut changer aucun
champ, y compris `name` et `sort`.

La suppression physique ou logique d'une ligne persistée socle est interdite,
même pour un superadministrateur. Le contrôle se fonde sur la valeur
persistée, afin qu'un basculement non sauvegardé ne permette pas de supprimer
la ligne. Les lignes locales conservent leur CRUD ordinaire.

Le Behavior ne reçoit ni identité ni rôle. Les Policies et
`FieldAuthorizationService` réservent la création et la mutation HTTP du
champ `base` au superadministrateur. Sur un département socle, la Policy
réserve également l'accès à l'édition. Les contrôleurs et le service des
départements refusent un basculement de `base` combiné à une modification
de champ ou d'association, avant toute persistance. Les refus de validation
Web/API sont présentés en français ; l'API utilise
`{success, message, errors}` et HTTP 422.

Les formulaires de références communes et de rôles exposent `base` au
superadministrateur, à la création et à l'édition. Le formulaire des
départements conserve son contrôle. Les autres champs d'une ligne socle
restent en lecture seule. Les réponses API exposent `base` ; aucune nouvelle
colonne de grille Tabulator n'est ajoutée.

## Justification

Le Behavior centralise une règle d'intégrité indépendante du transport et de
l'utilisateur. Les Policies et l'ACL de champ gardent la responsabilité de
l'autorisation HTTP, conformément aux ADR 0042 et 0065. L'activation explicite
évite les effets de bord sur les autres Tables ; les parcours spécialisés des
départements et des rôles restent cohérents avec leurs services existants.

## Conséquences et limites

Les écritures ORM standard sont protégées pour les neuf Tables. Les tests
d'intégration utilisent MySQL `daetf2_test` et les tests fonctionnels
vérifient les formulaires, les requêtes forgées, les basculements isolés et les
associations de département.

`updateAll`, `deleteAll` et le SQL direct ne déclenchent pas les callbacks
ORM et restent hors de cette protection. Aucun schéma, migration ou trigger
MySQL n'est introduit ; une protection de ces chemins exigerait une décision
distincte. Cet ADR demeure `Proposé` jusqu'à sa validation explicite.
