# ADR 0069 — Habilitations par rôles et périmètres

**Date :** 22 octobre 2026
**Statut :** Accepté
**Dépendances :** [ADR 0005](./0005-modele-de-donnees.md), [ADR 0028](./0028-authentification-evolutive-et-impersonate.md), [ADR 0041](./0041-segregation-donnees-model-custom-finders.md), [ADR 0042](./0042-acl-granulaire-niveau-champ-formulaire.md), [ADR 0050](./0050-semantique-perimetres-hierarchiques-utilisateur.md), [ADR 0054](./0054-commandes-ui-autorisees-par-domaine.md), [ADR 0055](./0055-workflow-validation-applicationforms.md), [ADR 0063](./0063-outrepassement-validations-workflow.md) et [ADR 0065](./0065-acl-automatique-des-mutations-web-api.md)

## Contexte

L'application possède des rôles, une attribution des options du menu général
(`role_menus`) et des autorisations de champs (`field_authorizations`). Plusieurs
droits d'action restent néanmoins définis par des identifiants ou des listes
de rôles dans le code. Modifier ces attributions exige alors un déploiement.

Des décisions distinctes peuvent aussi diverger : le finder actuel des
utilisateurs peut afficher dans une liste un collègue partageant un
département, alors que `UserPolicy::canView()` refuse sa fiche à un opérateur
non administrateur.

L'habilitation répond à trois questions successives :

1. **Quelle action ?** Lire des demandes, modifier un utilisateur ou administrer
   les menus.
2. **Sur quels objets ?** Ses propres objets, ceux de ses départements ou tous
   les objets concernés.
3. **Dans quelles conditions métier ?** Une demande peut être dans le périmètre
   de l'opérateur sans être modifiable à cause du workflow.

Les attributions configurables répondent aux deux premières questions. La
Policy reste l'arbitre de l'autorisation et consulte les règles métier pour
prendre sa décision.

## Décision

### Arbitrages de l'étape 3 du ticket 0026

Le ticket 0007 définit le socle générique des habilitations : le catalogue
`permissions`, les périmètres admissibles, les attributions aux rôles et la
séparation entre visibilité des menus et autorisation d'une action. Le ticket
0021 en est la première implémentation spécialisée, limitée dans sa première
livraison à l'administration des départements. Les deux tickets ne portent
donc pas deux modèles concurrents.

Le super-administrateur dispose d'un contournement explicite des permissions
et des périmètres organisationnels. Ce contournement ne s'étend ni aux
invariants métier, ni aux états du workflow, ni aux protections contre
l'auto-usurpation ou l'usurpation en cascade.

Pendant une session d'usurpation, seule l'identité effective usurpée est
évaluée. Les rôles, permissions, affectations départementales et périmètres
de l'administrateur d'origine ne sont jamais ajoutés à ceux de la cible.

Le modèle canonique est celui de cet ADR : `permission_scopes` décrit les
périmètres admissibles et `role_permissions` attribue ces périmètres aux
rôles. Une colonne `scope_type` portée directement par `role_permissions`
n'est pas une variante indépendante du modèle.

### Catalogue des permissions

`permissions` est le **catalogue des capacités reconnues par l'application**.
Une ligne nomme une action stable ; elle n'accorde aucun droit à elle seule.
Les Policies, les attributions aux rôles et les options du menu référencent la
même permission.

Le code public est calculé par `resource + "." + action`. Par exemple,
`applicationforms` et `read` donnent `applicationforms.read`. `resource` et
`action` sont en minuscules, sans point, et suivent une nomenclature stable.
Changer l'une de ces valeurs change le sens de la permission et exige une
migration explicite ; modifier son libellé ne change pas ce sens.

L'intention de la table figure dans sa création SQL, notamment dans le
commentaire de table. Le schéma cible suivant sera adapté à la version MySQL
effective avant l'écriture de la migration :

```sql
CREATE TABLE permissions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    resource VARCHAR(64) NOT NULL
        COMMENT 'Ressource protégée, par exemple applicationforms',
    action VARCHAR(64) NOT NULL
        COMMENT 'Capacité sur la ressource, par exemple read',
    code VARCHAR(129)
        GENERATED ALWAYS AS (CONCAT(resource, '.', action)) STORED
        COMMENT 'Identifiant stable calculé : resource.action',
    label VARCHAR(255) NOT NULL
        COMMENT 'Libellé français présenté dans l’administration des droits',
    PRIMARY KEY (id),
    UNIQUE KEY uq_permissions_resource_action (resource, action),
    UNIQUE KEY uq_permissions_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Catalogue des capacités applicatives : lien commun entre Policies, rôles et menus ; ne confère aucun droit à lui seul';
```

Les validations de la Table imposent la nomenclature de `resource` et `action`.
Le champ calculé `code` est en lecture seule pour l'ORM. Une attribution de
rôle et une option de menu utilisent l'identifiant de la permission : elles ne
conservent pas chacune une copie textuelle susceptible de diverger.

### Périmètres admissibles et attributions aux rôles

```text
permission_scopes
-----------------
permission_id → permissions.id
scope_type    self | assigned | all | unscoped
unique(permission_id, scope_type)

role_permissions
----------------
id
role_id       → roles.id
permission_id → permissions.id
scope_type
unique(role_id, permission_id, scope_type)
```

`permission_scopes` précise les périmètres **admissibles** pour chaque
permission. Une ligne de `role_permissions` attribue à un rôle l'un de ces
périmètres ; son couple `(permission_id, scope_type)` doit exister dans
`permission_scopes`. L'absence d'attribution vaut refus.

Un rôle peut recevoir plusieurs périmètres pour une même permission. Ils se
combinent par **OU** : `applicationforms.read` sur `self` et `assigned` signifie
« demandes créées par l'opérateur **ou** demandes de ses départements
explicitement affectés ». Un objet satisfaisant les deux conditions n'apparaît
qu'une fois. `all` rend les autres périmètres organisationnels inutiles pour
cette permission.

| Périmètre | Signification |
|---|---|
| `self` | Relation personnelle définie pour la ressource : créateur d'une demande ou propre fiche utilisateur, par exemple. |
| `assigned` | Relation avec les départements explicitement associés à l'opérateur dans `user_departments`. Aucun descendant n'est inféré implicitement, conformément à l'ADR 0050. |
| `all` | Aucun filtre de propriétaire ou de département pour **cette permission**. Les conditions métier restent applicables ; ce n'est pas le statut de super-administrateur. |
| `unscoped` | Les trois filtres génériques ne décrivent pas l'action. La Policy et, si nécessaire, un service métier appliquent une règle dédiée. Aucun contrôle n'est supprimé. |

`unscoped` ne signifie pas « réservé à l'administration » :

- `menus.manage` peut être `unscoped`, car « mon menu » ou « menu de mon
  département » ne constitue pas ici un périmètre utile. La Policy protège
  néanmoins l'administration.
- `applicationforms.vote` peut être `unscoped` : le droit d'essayer de voter ne
  suffit pas. Le rôle de l'étape, son état et l'éligibilité de l'opérateur sont
  vérifiés par le workflow.
- `users.impersonate` peut être `unscoped` tout en restant limité aux acteurs
  habilités et aux cibles admissibles, sans usurpation en cascade.

`all` décrit donc l'absence de filtre organisationnel sur une action filtrable
par lignes. `unscoped` renvoie à une règle propre à l'action, qui peut elle
aussi porter sur un objet précis.

### Dictionnaire initial des ressources et des actions

Le sens d'un périmètre est documenté pour chaque couple ressource–action. Ce
dictionnaire n'attribue, à lui seul, aucun droit à un rôle. Une combinaison
n'est inscrite dans `permission_scopes` qu'après décision et tests.

| Permission | `self` | `assigned` | Autre condition |
|---|---|---|---|
| `applicationforms.read` | Demande créée par l'opérateur (`user_id`). | Département de la demande affecté à l'opérateur. | `all` : lecture sans filtre organisationnel. |
| `applicationforms.update` | Même définition du créateur. | Même définition du département. | Le workflow peut refuser la modification, même avec `all`. |
| `users.read` | Propre fiche de l'opérateur (`users.id`). | Utilisateur partageant au moins un département explicite. | `all` : lecture globale ; champs affichés contrôlés séparément. |
| `users.update` | Propre fiche. | Utilisateur partageant un département, si cette capacité est retenue. | Protection des champs sensibles et contre l'élévation de privilèges. |
| `departments.read` | Sans objet. | Département explicitement affecté. | `all` : tous les départements. |
| `validationsequences.read` | Sans objet. | Séquence d'un département affecté. | `all` : toutes les séquences. |
| `menus.manage` | Sans objet. | Sans objet. | `unscoped` : Policy d'administration. |
| `applicationforms.vote` | Aucun filtre générique suffisant. | Aucun filtre générique suffisant. | `unscoped` : rôle et état de l'étape, éligibilité déterminée par le workflow. |

Les actions supplémentaires et leurs attributions sont fixées par la matrice
ci-dessous. La permission de lecture n'accorde jamais implicitement la
modification ou le vote. Le vote conserve
les règles de l'ADR 0055 : être créateur d'une demande peut rendre éligible à
l'étape « demandeur », mais pas à toute étape ultérieure. Une évolution des
droits de suppléance de l'ADR 0063 demande une décision explicite.

### Matrice retenue au point 5 du ticket 0007 — 18 novembre 2026

La matrice conserve le socle du point 4 et ajoute les actions utilisateurs,
le workflow des DAE et les capacités d’administration. Elle décrit
**42 permissions**, **52 périmètres admissibles** et **76 attributions**.
Les rôles 1 à 7 sont respectivement Admin, Demandeur et les cinq rôles de
validation. Aucun rôle ordinaire ne reçoit `all` dans cette reprise.

| Permission | Périmètres admissibles | Attributions ordinaires retenues |
|---|---|---|
| `applicationforms.create` | `self` | Rôles 1 et 2 : `self` |
| `applicationforms.read`, `applicationforms.update` | `self`, `assigned`, `all` | Rôle 1 et rôles 3–7 : `self`, `assigned` ; rôle 2 : `self` |
| `applicationforms.delete` | `self` | Rôles 1–7 : `self` |
| `applicationforms.vote` | `unscoped` | Rôles 1–7 |
| `applicationforms.launch_validation` | `unscoped` | Rôles 1–7 |
| `applicationforms.reset_validation`, `applicationforms.remind_validation`, `applicationforms.override_validation` | `unscoped` | Rôle 1 |
| `applicationforms.archive` | `unscoped` | Rôles 1 et 7 |
| `users.read` | `self`, `assigned`, `all` | Rôles 1–7 : `self` ; rôle 1 : aussi `assigned` |
| `users.update` | `self`, `assigned` | Rôles 1–7 : `self` ; rôle 1 : aussi `assigned` |
| `users.create`, `users.delete`, `users.change_role` | `unscoped` | Rôle 1 |
| `users.associate_departments` | `assigned` | Rôle 1 |
| `users.impersonate` | `unscoped` | Aucune |
| `departments.read`, `departments.manage` | `assigned`, `all` | Rôle 1 : `assigned` |
| `references.read`, `references.create`, `references.update`, `references.delete` | `unscoped` | Aucune |
| `roles.read`, `roles.create`, `roles.update`, `roles.delete` | `unscoped` | Aucune |
| `fieldauthorizations.read`, `fieldauthorizations.create`, `fieldauthorizations.update`, `fieldauthorizations.delete` | `unscoped` | Aucune |
| `workflowsettings.read`, `workflowsettings.update` | `unscoped` | Aucune |
| `validationcommenttemplates.read`, `validationcommenttemplates.create`, `validationcommenttemplates.update`, `validationcommenttemplates.delete` | `unscoped` | Aucune |
| `validationsequences.read` | `assigned`, `all` | Aucune |
| `validationsequences.create`, `validationsequences.update`, `validationsequences.delete` | `assigned` | Aucune |
| `menus.manage` | `unscoped` | Aucune |

`unscoped` impose un contrôle explicite dans la Policy ou le service métier.
Les utilisateurs conservent les contrôles du rôle Admin, de gérabilité, des
rôles fonctionnels, des champs et des départements réellement modifiés.
L’usurpation reste réservée au super-administrateur, sans cascade ; seule
l’identité effective est évaluée. Le lancement, la réinitialisation, la
relance, l’archivage et l’outrepassement des DAE conservent les contrôles
historiques de visibilité, de créateur, de rôle, de contrat et d’état du
workflow. L’outrepassement effectif vérifie aussi sa permission dans le
service transactionnel.

Les Policies des références communes, des rôles, des autorisations de champs,
du paramétrage global et des commentaires prédéfinis consultent désormais
leur permission par action. Sans attribution ordinaire, leur accès reste
réservé au contournement du super-administrateur ; une attribution
exceptionnelle explicite peut ouvrir l’action concernée. Les références et
rôles socles restent non supprimables, y compris par un super-administrateur.
Les codes CGR conservent les contrôles actif/système et leur règle distincte
`canAssociate`. Les exceptions existantes `MenuPolicy` et `PermissionPolicy`
utilisent toujours `menus.manage` ; la gestion spécialisée des circuits de
validation et de leurs gestionnaires locaux reste inchangée.

Pour les séquences, les lectures utilisent `validationsequences.read` et ses
périmètres directs ou `all`. Les écritures utilisent respectivement `create`,
`update` et `delete`, exclusivement sur `assigned`, puis contrôlent chaque
département concret. Un ancien périmètre `all` non admissible ne permet pas
de contourner ce contrôle. L’API distingue l’affectation (`add`), l’ordre et
le délai (`edit`), et le retrait (`delete`). La sélection des rôles actifs
n’impose pas `menus.manage`. Les GET du paramétrage global utilisent
`workflowsettings.read` ; les POST utilisent `workflowsettings.update`.

La migration distincte `20261118150000_GeneralizeDomainPermissions.php`
complète le catalogue, les périmètres et les attributions manquants par
`INSERT ... WHERE NOT EXISTS`. Elle ne remplace ni ne supprime de libellé,
de périmètre ou d’attribution préexistants. Elle signale les libellés
inattendus, périmètres hors matrice et attributions divergentes, notamment
les attributions ordinaires des permissions qui doivent rester sans droit.
Son `down()` ne retire aucune donnée et ne défait aucune migration antérieure.
La migration du point 4 `ResumeGenericPermissions` reste séparée et intacte.
Les trois fixtures de permissions reprennent la matrice complète.

Vérification sans base : 125 tests ciblés de Policies et services réussis
(639 assertions) avec `phpunit.unit.xml`, après les échecs RED attendus ;
syntaxe valide sur les 53 fichiers PHP ajoutés ou modifiés du checkout et
`git diff --check` sans erreur. Les nouveaux scénarios HTTP/API sont
préparés mais non exécutés : leur bootstrap appliquerait les migrations.
**Aucune migration n’a été appliquée à `daetf2_test` ni à `daetf`.** La
validation sur base et la comparaison des accès restent aux points 6 et 7.

### Menu général et accès direct

Chaque option menant à une page référence la permission nécessaire pour
**entrer dans cette page** : `menus.required_permission_id → permissions.id`.
Une option « Demandes » peut exiger `applicationforms.read`. Elle apparaît si
l'opérateur possède cette permission dans au moins un périmètre et si la
Policy autorise l'entrée. La même permission protège l'ouverture directe de la
page et ses points d'entrée API.

Un opérateur doté de `self` **et** `assigned` voit une seule option « Demandes ».
Une rubrique sans destination apparaît si au moins un enfant est visible. La
présence du lien ne garantit pas qu'une demande précise soit accessible : le
périmètre et la Policy en décident pour chaque objet.

`role_menus` reste en service pendant la transition. Ses attributions sont
inventoriées avant migration, notamment lorsque plusieurs options mènent à
une même destination. La signification de son `department_id` est examinée
séparément : le chargement actuel du menu général ne l'utilise pas pour
filtrer les options. La table n'est retirée qu'après comparaison des droits
avant et après migration.

### Policy, finders et règles métier

Pour une action sur un objet, la décision est :

```text
permission accordée au rôle
ET objet dans au moins un périmètre accordé, si applicable
ET conditions métier satisfaites
```

La **Policy est l'arbitre de l'autorisation**. Elle consulte les permissions,
le périmètre de l'objet et les règles du domaine. Les **finders ORM** appliquent
les périmètres aux listes en SQL. Pour une même action de lecture, finder et
Policy partagent la même définition du périmètre : un objet présenté comme
lisible dans la liste ne doit pas être refusé sur sa fiche à cause d'un second
filtre contradictoire.

Les **Tables et services métier** portent les règles liées à l'état de
l'objet. Le service qui exécute une mutation revérifie les invariants
nécessaires au moment de l'écriture. `field_authorizations` protège toujours
les champs : pouvoir modifier un objet ne permet pas de modifier tous ses
champs.

Le comportement du super-administrateur est explicite et testé. Son éventuel
contournement des attributions ou des périmètres organisationnels ne contourne
pas les invariants métier. En session d'usurpation, l'identité effective ne
récupère pas silencieusement les droits de l'administrateur d'origine.

Modifier le catalogue ou les attributions est une opération sensible. Ses
Policies empêchent l'élévation de privilèges non autorisée et préservent une
capacité de récupération administrative.

## Exemple de bout en bout

Alice, gestionnaire, possède `applicationforms.read` sur `self` et `assigned`.
Elle est explicitement associée aux départements 4 et 9.

1. L'option « Demandes » apparaît une seule fois dans son menu.
2. Le finder inclut les demandes qu'elle a créées **ou** celles des
   départements 4 et 9, sans doublon.
3. La Policy refuse la fiche d'une demande extérieure à ces deux ensembles,
   même si Alice saisit directement son URL.
4. Cette permission de lecture ne lui permet ni de modifier ni de voter sur
   une demande.

## Justification et avantages recherchés

- **Attributions modifiables sans déploiement** : accorder une capacité à un
  rôle devient une modification contrôlée de données, sans éditer une liste de
  rôles dans le PHP.
- **Décision cohérente** : menu, page Web, API, finder et Policy s'appuient sur
  la même permission et la même définition du périmètre.
- **Moindre privilège** : un rôle peut lire sans modifier, ou agir sur `self`
  et `assigned` sans recevoir `all`.
- **Droits observables** : les capacités, périmètres admissibles et
  attributions effectives se consultent et s'auditent en base.
- **Responsabilités séparées** : les données portent les attributions
  variables ; les Models et services portent la signification des règles ;
  les Policies prennent la décision d'autorisation.
- **Évolution progressive** : chaque ressource est intégrée après définition
  de ses permissions, de ses périmètres et de ses tests, sans remplacer les
  règles du workflow ou l'ACL des champs.

Le catalogue explicite évite de faire dépendre la sécurité d'un libellé de
menu ou d'une URL mutable. La séparation entre action, périmètre et condition
métier limite la duplication et garde les exceptions compréhensibles.

## Conséquences et vérification

L'ajout d'une permission exige de définir son sens, ses périmètres admissibles,
sa correspondance avec les actions Web et API et les tests associés. Le
catalogue et le dictionnaire sont mis à jour ensemble. La reprise des droits
historiques est explicite : tout écart est identifié et validé avant
activation, sans découler implicitement d'une association `role_menus`.

Les tests couvrent au minimum le refus sans attribution, `self`, `assigned`,
leur union, `all`, une action `unscoped`, la cohérence liste–fiche, le menu et
l'accès direct, ainsi que les restrictions métier, les champs sensibles, le
super-administrateur et l'usurpation. Les différences de droits entre deux
opérateurs du même rôle mais de départements différents sont vérifiées.

Le modèle ajoute un catalogue et des règles de cohérence à maintenir. Une
permission ou un périmètre mal initialisé peut retirer un accès légitime ; une
attribution trop large peut l'étendre. Le déploiement se fait donc ressource
par ressource avec comparaison des droits avant et après migration.
