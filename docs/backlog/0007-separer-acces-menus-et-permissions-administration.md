# 0007 — Séparer les accès aux menus des permissions d'administration

**Statut :** Prêt

**Priorité :** Haute

## Contexte

Les Policies de menus déterminent aujourd'hui plusieurs autorisations au moyen de rôles codés en dur. Cette approche est simple tant que les rôles sont peu nombreux, mais elle confond deux notions distinctes : l'accès à un écran métier et l'administration transversale de l'application.

La table `role_menus` associe déjà un rôle à une option de menu, avec un éventuel département. Elle reste en service pendant la transition, mais ne constitue pas une permission d'action. Son `department_id` n'est actuellement pas utilisé par le chargement du menu général pour filtrer les options : sa signification doit être inventoriée séparément. Le modèle cible de l'ADR 0069 fait référencer à chaque menu la permission nécessaire à l'entrée dans sa page, sans confondre navigation et administration des droits.

Le ticket 0007 définit le socle générique des habilitations. Le ticket 0021
en constitue la première implémentation spécialisée pour l'administration des
départements ; il ne doit pas introduire un modèle de permissions concurrent.

## Illustration

Pour les rôles suivants :

| Rôle | Identifiant |
|---|---:|
| Administrateur | 1 |
| Gestionnaire | 2 |
| Agent | 3 |

et les menus métier suivants :

| Option de menu | Identifiant |
|---|---:|
| Saisie demande | 10 |
| Validation demande | 11 |
| Gestion des menus | 90 |

`role_menus` décrit historiquement les associations de navigation :

| role_id | menu_id | Effet |
|---:|---:|---|
| 1 | 10 | L'administrateur saisit une demande. |
| 1 | 11 | L'administrateur valide une demande. |
| 2 | 10 | Le gestionnaire saisit une demande. |
| 3 | 10 | L'agent saisit une demande. |

Ajouter `role_id = 3, menu_id = 11` associe l'option de validation à l'agent. Cette association ne lui accorde pas à elle seule le droit de valider : dans le modèle cible, la permission d'entrée, la Policy et les règles du workflow restent nécessaires.

Dans le modèle cible, `permissions` est le catalogue des capacités ;
`permission_scopes` décrit leurs périmètres admissibles et
`role_permissions` porte leurs attributions explicites. Exemple d'attributions
à confirmer par la matrice, sans droit implicite lié au numéro du rôle :

| role_id | Permission | Périmètre | Effet |
|---:|---|---|---|
| 1 | `users.read` | `all` | Consulter les utilisateurs si cette attribution est retenue. |
| 2 | `applicationforms.read` | `assigned` | Consulter les demandes des départements affectés. |

Le gestionnaire peut ainsi utiliser « Saisie demande » sans pouvoir accéder à `/menus`. Conformément au ticket 0006, un Admin ordinaire (`role_id = 1`, `issuperuser = false`) ne reçoit pas `menus.manage` et reste exclu de l'administration des menus. Le super-administrateur bénéficie du contournement explicite des permissions et périmètres prévu par l'ADR 0069 ; ce statut est distinct du rôle Admin et ne contourne ni les invariants métier ni les restrictions du workflow.

## Objectif

Faire reposer l'entrée dans les pages, la navigation et les actions d'administration sur les permissions explicites de l'ADR 0069. Conserver `role_menus` jusqu'à validation de la comparaison des droits avant/après. Les Policies restent la frontière de sécurité et consultent les attributions, les périmètres et les conditions métier plutôt que comparer des identifiants de rôles.

### Choix d'architecture pour la première version

La première version adopte une autorisation hybride fondée sur les permissions
génériques, les Policies CakePHP et les custom finders SQL. Elle ne remplace
pas ce modèle par une ACL classique ARO/ACO.

Une ACL ARO/ACO est adaptée à la question « quel acteur peut exécuter quelle
action sur quelle ressource ? ». Elle ne fournit toutefois pas à elle seule le
filtrage des lignes par propriétaire ou par département, le calcul des
départements descendants, ni les restrictions liées au workflow. Ces règles
devraient malgré tout rester dans les Policies et les requêtes métier.

L'option ARO/ACO pourra être réévaluée ultérieurement si l'application doit
accorder ou refuser des droits arbitraires sur un grand nombre de ressources
individuelles. Elle n'est pas introduite dans ce ticket afin d'éviter une
migration et une double modélisation des autorisations avant qu'un besoin
confirmé ne le justifie.

## Modèle générique des permissions

Le modèle doit rester indépendant des ressources métier particulières. Une
permission est définie par une ressource et une action, puis attribuée à un
rôle avec un périmètre explicite :

```text
permissions
-----------
id, code, resource, action

permission_scopes
-----------------
permission_id, scope_type

role_permissions
----------------
id, role_id, permission_id, scope_type
```

La table `permission_scopes` définit les périmètres admissibles ;
`role_permissions` attribue l'un de ces périmètres à un rôle. Cette
distinction suit l'ADR 0069 et permet de refuser une combinaison non déclarée
avant toute attribution.

Les permissions suivent une nomenclature stable telle que :

```text
applicationforms.create
applicationforms.read
applicationforms.update
applicationforms.delete
```

Le champ `scope_type` accepte les valeurs suivantes :

| Valeur | Signification |
|---|---|
| `self` | L'objet appartient à l'opérateur connecté (`user_id = identity.id`). |
| `assigned` | L'objet est rattaché à l'un des départements directement affectés à l'opérateur dans `user_departments`. |
| `all` | Aucun filtrage organisationnel n'est appliqué pour cette permission. |
| `unscoped` | Une règle propre à l'action est appliquée par la Policy ou un service, sans supprimer les contrôles. |

`assigned` est préféré à `department` : le premier décrit la règle d'accès
fondée sur les affectations de l'opérateur, tandis que le second ne décrit
que la nature d'une colonne métier. `all` ne signifie pas automatiquement
« superadministrateur » : il s'agit d'un périmètre explicite qui peut être
accordé en lecture seule à un auditeur ou à un compte de reporting. L'absence
de permission vaut refus.

Les menus et les permissions restent deux décisions distinctes :

- `menus.required_permission_id` référence la permission nécessaire pour entrer dans la page ; une rubrique sans destination est visible si au moins un enfant est visible ;
- `role_menus` reste en service pendant la transition, jusqu'à validation des écarts ;
- `role_permissions` détermine les actions autorisées ;
- la Policy et le finder SQL réévaluent toujours l'identité, le périmètre et
  l'état métier de l'objet.

### Exemple : rôle demandeur

Le rôle `demandeur` peut recevoir uniquement les permissions suivantes :

| Permission | Périmètre | Effet |
|---|---|---|
| `applicationforms.create` | `self` | Créer une DAE pour son propre compte. |
| `applicationforms.read` | `self` | Consulter ses DAE. |
| `applicationforms.update` | `self` | Modifier ses DAE tant que le workflow l'autorise. |
| `applicationforms.delete` | `self` | Supprimer ses DAE tant qu'aucun workflow n'est lancé. |

Lors de la création, le serveur impose l'identité du propriétaire et ignore
un éventuel `user_id` fourni par le client. Les listes sont filtrées en SQL et
une URL ou une API directe ne permet jamais de consulter ou modifier la DAE
d'un autre utilisateur.

Le demandeur ne reçoit aucune permission implicite sur les utilisateurs,
rôles, menus, séquences, paramètres de workflow, validations ou
autorisations de champs. Les droits de champs complémentaires restent
déclarés dans `field_authorizations` lorsque le besoin métier l'exige.

### Complexité différée des périmètres

La V1 réutilise `user_departments` et ne calcule pas encore les descendants
d'un département pour le périmètre `assigned`. Cette limitation est
intentionnelle : elle permet de valider le modèle générique et les contrôles
de sécurité avec un nombre réduit de règles.

Une évolution ultérieure pourra ajouter, de manière générique :

```text
user_permission_grants
----------------------
id, user_id, permission_id, scope_type, hierarchy_mode
```

avec par exemple `hierarchy_mode = exact | descendants`. Elle permettra à
deux utilisateurs ayant le même rôle de disposer de périmètres différents :

- un demandeur limité à son département exact ;
- un demandeur autorisé sur ce département et ses sous-départements.

Cette évolution introduira des règles de priorité entre les droits du rôle et
ceux de l'utilisateur, des règles de fusion ou de conflit, une interface
d'administration, une résolution de la hiérarchie et des migrations ainsi
que des tests supplémentaires. Elle ne devra être engagée qu'après
confirmation du besoin métier et restera basée sur les mêmes tables
génériques, sans table spécifique aux DAE.

## Feuille de route restante

Le point de départ connu de la base de développement `daetf` est de
**3 permissions**, **5 périmètres admissibles**, **2 attributions de rôle** et
**28 associations `role_menus`**. La colonne `menus.required_permission_id`
n'existe pas encore. Ces constats ne constituent pas une reprise complète.
Les étapes suivantes restent à réaliser ; leur décomposition rend le ticket
prêt, sans démarrer son implémentation.

- [x] **1. Inventorier l'existant.** Relever les permissions et règles dans les Policies, finders et routes Web/API ; comparer le comportement au catalogue, aux périmètres et attributions de `daetf` et aux 28 associations `role_menus`. Examiner séparément `department_id` et les destinations partagées ; distinguer les droits déductibles des exceptions à arbitrer.
- [x] **2. Fixer la matrice des permissions.** Définir pour chaque capacité son code, ses périmètres admissibles et ses attributions aux rôles, conformément à l'ADR 0069. Préserver le refus d'administration des menus pour l'Admin ordinaire du ticket 0006 et l'exception explicite du super-administrateur.
- [x] **3. Automatiser la reprise des données.** Écrire une migration répétable qui préserve les lignes existantes et complète le catalogue `permissions`, les périmètres admissibles `permission_scopes` et les attributions déterminables `role_permissions`. Ajouter `menus.required_permission_id` et les liens menu déductibles sans ambiguïté ; signaler les autres. Ne pas attribuer `menus.manage` à l'Admin ordinaire.
- [x] **4. Sécuriser la transition des menus.** Vérifier les mêmes permissions pour la visibilité et l'entrée directe par URL/API, avec les Policies et périmètres applicables. Traiter les rubriques sans destination et les associations non migrables automatiquement. Garder `role_menus` jusqu'à validation de la comparaison des droits avant/après.
- [x] **5. Généraliser par domaine.** Avancer par volets indépendamment vérifiables : utilisateurs et périmètres ; DAE et règles de workflow ; puis références et autres fonctions d'administration. Conserver les invariants métier et du workflow dans leurs Policies et services, ainsi que les protections de champs et d'usurpation.
- [ ] **6. Compléter la base de développement.** Valider la migration sur `daetf2_test`, vérifier explicitement la connexion cible, puis l'appliquer à `daetf`. Configurer uniquement les attributions exceptionnelles non déductibles avec l'API protégée existante et les consigner ; ne pas utiliser de SQL manuel. La page Web du catalogue reste en lecture seule, sans nouvelle interface de gestion.
- [ ] **7. Vérifier et clôturer.** Comparer les accès avant/après pour les comptes et périmètres repris, documenter les écarts et exceptions validés, puis actualiser les critères d'acceptation et le statut pour clôturer le ticket. Prévoir les vérifications unitaires, d'intégration et HTTP/API ci-dessous, puis `make test.all` sur `daetf2_test`.

### Résultats du point 1 — 18 novembre 2026

#### Code et points d'entrée

- `PermissionResolver` applique le refus par défaut, lit les attributions
  `role_permissions` et laisse passer explicitement le super-administrateur.
  Pour `assigned`, le périmètre est calculé depuis les associations directes
  `user_departments`; aucun descendant n'est ajouté.
- `DepartmentPolicy` et `DepartmentsTable::findVisibleTo` utilisent les
  permissions génériques `departments.read` et `departments.manage`. Le
  finder n'enlève le filtre des départements que si `departments.read` a le
  périmètre `all`; sinon il filtre sur les départements directement affectés.
- Les autres règles principales restent codées dans les Policies et finders
  métier. `ApplicationformPolicy` et `ApplicationformsTable::findVisibleTo`
  utilisent le créateur, le département affecté, des rôles historiques et
  l'état du workflow. `UserPolicy` utilise des rôles autorisés, l'identité
  propre et des contrôles de gestionabilité ; `UsersTable::findVisibleTo`
  filtre sur le partage d'un département. Une fiche peut donc être refusée
  par la Policy après avoir été incluse dans la liste. Les autorisations de
  champs restent distinctes dans `field_authorizations`.
- `MenuPolicy` et `PermissionPolicy` protègent la gestion des menus et des
  attributions par `menus.manage` sur `unscoped`. L'écran Web `MenusController`
  impose en plus le statut super-administrateur. Les Policies de références,
  de séquences, de rôles et plusieurs Policies d'administration continuent
  d'utiliser les règles historiques, souvent réservées au super-administrateur
  ou à des rôles nommés.
- Les routes Web utilisent les routes de repli CakePHP ; l'API déclare des
  routes explicites puis des ressources REST. Les routes ne définissent pas
  une matrice de permission : les contrôleurs appellent leurs Policies. Les
  opérations d'administration des menus et des permissions sont gardées par
  `MenuPolicy` et `PermissionPolicy`. À l'inverse, `GET /api/menus.json`
  saute l'autorisation générique et filtre l'arbre avec `role_menus` et le
  rôle de l'opérateur. Ce chargement ignore `department_id` et ne référence
  pas de permission d'entrée.
- Aucun usage de `menus.required_permission_id` ni de `MenuAccessService`
  n'existe dans le code ou les migrations présents. Le menu général dépend
  encore des associations `role_menus`.

Références de code : [PermissionResolver](../../src/Service/Security/PermissionResolver.php),
[MenuPolicy](../../src/Policy/MenuPolicy.php),
[DepartmentPolicy](../../src/Policy/DepartmentPolicy.php),
[ApplicationformPolicy](../../src/Policy/ApplicationformPolicy.php),
[UserPolicy](../../src/Policy/UserPolicy.php),
[MenusTable](../../src/Model/Table/MenusTable.php),
[UsersTable](../../src/Model/Table/UsersTable.php),
[ApplicationformsTable](../../src/Model/Table/ApplicationformsTable.php),
[routes Web/API](../../config/routes.php).

#### Catalogue et attributions de `daetf`

La connexion de développement a été vérifiée comme pointant sur `daetf` ; la
migration `CreateGenericPermissions` y est appliquée. L'inventaire en lecture
seule donne :

| Permission | Périmètres admissibles | Attribution existante |
|---|---|---|
| `departments.read` | `assigned`, `all` | rôle 1 : `assigned` |
| `departments.manage` | `assigned`, `all` | rôle 1 : `assigned` |
| `menus.manage` | `unscoped` | aucune |

Il y a donc 3 permissions, 5 périmètres admissibles et 2 attributions. Le
périmètre admissible `all` n'est pas accordé au rôle 1. Aucun droit n'est
attribué aux rôles 2 à 7 dans `role_permissions`. Le super-administrateur
reste un contournement séparé du catalogue.

#### Les 28 associations de navigation

Dans cette base, les rôles réels sont Admin (1), Demandeur (2), puis les cinq
rôles de validation (3 à 7). Cette répartition diffère des identifiants
illustratifs du ticket.

| Rôle | Associations `role_menus` |
|---|---|
| 1 — Admin | 22 : menus 6–16, puis 18, 20, 22, 24 et 26–32 |
| 2 — Demandeur | 1 : menu 7 « Saisie Demande » |
| 3 à 7 — Valideurs | 1 chacun : menu 7 « Saisie Demande » |

Les 28 lignes ont `department_id = NULL`. La colonne reste facultative dans
le schéma et dans la validation de `RoleMenusTable`, mais aucune association
actuelle ne fournit de périmètre départemental. Le chargement de
`/api/menus.json` ne lit de toute façon pas cette colonne.

Trois paires de menus ont la même destination après normalisation de la casse
et des espaces :

| Destination | Options de menu |
|---|---|
| `users` | 4 « Utilisateurs » (`users`), 13 « Utilisateurs » (`Users`) |
| `validationsequences` | 15 « Association Séquence Department », 25 « Validation séquences et workflow » |
| `contracttypes` | 16 « Types de Contrat » (`Contracttypes`), 26 « Types de contrats » (`contracttypes`) |

Ces options ne sont pas toutes associées aux mêmes rôles ; elles doivent être
traitées comme des destinations partagées à vérifier, et non comme des
permissions distinctes déductibles de leur seul libellé.

#### Droits déductibles et exceptions

- `role_menus` décrit la navigation, pas l'autorisation d'une action. Le menu
  7 pointe vers `applicationforms/add` pour les rôles 1 à 7, mais la création
  est autorisée par `ApplicationformPolicy` au Demandeur, à l'Admin et au
  super-administrateur. Les liens des rôles de validation ne leur accordent
  donc pas le droit de créer.
- Le rôle Admin possède aussi le menu 8 d'association des menus, alors que
  `menus.manage` ne lui est pas attribué et que l'écran Web renvoie les
  non-super-administrateurs à l'accueil. Cette association historique ne
  doit pas devenir une attribution de `menus.manage`, conformément au ticket
  0006.
- `MenusTable::findRoleAccessVisibleTo` et `RolesTable::findRoleAccessVisibleTo`
  admettent encore le rôle Admin, tandis que `MenuPolicy` exige
  `menus.manage` et le contrôleur Web exige le statut super-administrateur.
  Le parcours d'administration des associations contient ainsi des règles
  divergentes qui doivent être arbitrées au point 4.
- Les associations aux menus des utilisateurs, rôles, références et
  configurations de workflow ne déterminent pas les permissions `read`,
  `update` ou `manage` ni leurs périmètres. Leurs Policies gardent des
  règles historiques distinctes ; aucune reprise automatique n'est justifiée
  sans matrice validée.
- Les rubriques sans destination (`#`), les destinations partagées et les
  éventuelles différences d'accès à une même page restent à traiter avant
  de renseigner `menus.required_permission_id`. Aucun lien de menu ne suffit
  à déduire une attribution `role_permissions`.

### Résultats du point 2 — 18 novembre 2026

Les rôles de `daetf` sont Admin (1), Demandeur (2) et Valideurs (3 à 7).
`assigned` couvre uniquement les départements directement associés à
l'opérateur dans `user_departments`. Les périmètres attribués à une même
permission se combinent par OU, conformément à l'ADR 0069.

| Permission | Périmètres admissibles | Attributions explicites |
|---|---|---|
| `applicationforms.create` | `self` | Rôles 1 et 2 : `self` |
| `applicationforms.read` | `self`, `assigned`, `all` | Rôle 1 et rôles 3–7 : `self`, `assigned` ; rôle 2 : `self` |
| `applicationforms.update` | `self`, `assigned`, `all` | Rôle 1 et rôles 3–7 : `self`, `assigned` ; rôle 2 : `self` ; la Policy et le workflow restent déterminants |
| `applicationforms.delete` | `self` | Rôles 1–7 : `self`, sous réserve du propriétaire et de l'absence de workflow |
| `applicationforms.vote` | `unscoped` | Rôles 1–7 ; le workflow conditionne le vote du Demandeur et des Valideurs ; l'Admin ne peut qu'outrepasser une étape selon les règles existantes |
| `users.read` | `self`, `assigned`, `all` | Tous les rôles : `self` ; rôle 1 : aussi `assigned` |
| `users.update` | `self`, `assigned` | Tous les rôles : `self` ; rôle 1 : aussi `assigned` ; protections de champs et de rôle maintenues |
| `departments.read` | `assigned`, `all` | Rôle 1 : `assigned`, comme dans `daetf` |
| `departments.manage` | `assigned`, `all` | Rôle 1 : `assigned`, comme dans `daetf` |
| `validationsequences.read` | `assigned`, `all` | Aucun rôle ordinaire pour l'instant ; réexaminer au point 5 |
| `menus.manage` | `unscoped` | Aucun rôle ordinaire, en particulier pas l'Admin 1 |

Aucun rôle ne reçoit le périmètre `all` dans cette matrice. Il reste
admissible uniquement pour les permissions qui le déclarent ; toute
attribution future devra être explicite. L'absence d'attribution vaut refus.
Le super-administrateur conserve le contournement explicite de l'ADR 0069.

Les règles de Policy, les conditions métier, le workflow, les protections de
champs et l'identité effective en usurpation continuent de s'appliquer. Les
permissions non couvertes par ce socle — notamment création, suppression,
changement de rôle ou usurpation d'utilisateurs, ainsi que lancement et
administration du workflow DAE — restent sous leurs Policies actuelles et
sont différées au point 5. `role_menus` ne permet pas de déduire ces droits.

### Résultats du point 3 — 18 novembre 2026

La migration `ResumeGenericPermissions` complète les onze permissions, les
vingt-et-un périmètres admissibles et les soixante attributions fixés au
point 2. Chaque insertion vérifie l'absence de la même clé ; les libellés,
périmètres et attributions préexistants ne sont ni remplacés ni supprimés.
Elle ajoute `menus.required_permission_id` comme entier non signé nullable,
son index et une clé étrangère `ON DELETE RESTRICT`. `down()` retire ces
éléments de schéma et conserve les permissions, périmètres et attributions.
La migration affiche aussi les entrées du catalogue, périmètres et
attributions qui divergent de la matrice.

Les URL sont normalisées en minuscules après suppression des espaces
périphériques et des barres obliques en début et fin, puis comparées à une
liste explicite :

| URL | Permission référencée |
|---|---|
| `applicationforms/add` | `applicationforms.create` |
| `departments` | `departments.read` |
| `departments/assignManagers`, `departments/assignServices` | `departments.manage` |
| `menus`, `menus/add`, `Menus/role_access` | `menus.manage` |
| `users` | `users.read` |
| `Validationsequences` | `validationsequences.read` |

Les associations `role_menus` et les libellés n'interviennent pas dans cette
correspondance. La matrice n'accorde toujours pas `menus.manage` au rôle 1.
Une lecture seule de la base `daetf` confirme trois permissions déjà présentes,
cinq périmètres admissibles et deux attributions, tous conformes à la matrice.
Sur les données observées, la migration ajouterait huit permissions, seize
périmètres et cinquante-huit attributions. Elle associerait onze des
trente-trois menus ; les vingt-deux autres resteraient à `NULL` et seraient
affichés avec leur identifiant, URL et nom :

| ID | URL | Nom |
|---:|---|---|
| 1 | vide | Administration |
| 5 | `users/add` | Utilisateur ajout |
| 6 | `UserDepartments/assocDepartmentUsers` | Association Service Utilisateur |
| 9 | `#` | Associations |
| 10 | `UserDepartments/assocUserDepartments` | Association Utilisateur Service |
| 11 | `#` | Références |
| 14 | `Roles` | Rôles |
| 16 | `Contracttypes` | Types de Contrat |
| 17 | `Contracttypes/indexDyna` | Types Contrat UI dynamique Beta test |
| 19 | `cgr-strategies` | Stratégies CGR |
| 21 | `Tools/triggerDailyDigest` | Envoi email quotidien (préférez la CLI) |
| 22 | `cgr-codes` | CGR Codes |
| 23 | `field-authorizations` | Matrice des droits ApplicationForm |
| 24 | `users/bulk-departments` | Association massive user department |
| 26 | `contracttypes` | Types de contrats |
| 27 | `hiringreasons` | Motifs de recrutement |
| 28 | `professionalcategories` | Catégories professionnelles |
| 29 | `worktimes` | Temps de travail |
| 30 | `periods` | Périodicités |
| 31 | `budgetfeatures` | Caractéristiques budgétaires |
| 32 | `yesnos` | Réponses Oui / Non |
| 33 | `workflow-settings` | Paramétrage global du cycle |

La migration n'a pas été appliquée à `daetf` ni à `daetf2_test` ; son
application et la validation des écarts relèvent du point 6.

### Résultats du point 4 — 18 novembre 2026

`MenuAccessService` filtre récursivement l’arbre historiquement éligible de
`GET /api/menus.json`, sans changer l’ordre ni la structure des branches
conservées. Un menu lié par `required_permission_id` exige son association
`role_menus` et la permission sur un périmètre applicable. Une URL non
mappée conserve la visibilité historique ; une rubrique vide ou `#` ne
subsiste qu’avec un enfant visible. Le super-administrateur conserve son
contournement des permissions et des associations. Le menu synthétique
« Cycles bloqués » est toujours ajouté séparément selon sa règle actuelle.
Aucune association `role_menus` n’est modifiée et son `department_id` reste
ignoré par la navigation, comme auparavant.

Les Policies DAE vérifient désormais `applicationforms.read`, `create`,
`update`, `delete` et `vote`, avec les périmètres propriétaire, département
direct ou global explicitement accordés. La duplication requiert aussi la
permission de création. Les restrictions de création, de propriétaire,
d’édition selon le rôle et de workflow restent applicables ; les actions
hors matrice (lancement, réinitialisation, relance et archivage du cycle)
conservent leurs règles et leur contrôle historique de visibilité.

`UserPolicy` vérifie `users.read` et `users.update` sur l’identité propre ou
le partage d’un département direct, avec `all` admissible en lecture. Les
contrôles historiques de rôle et de gérabilité restent en place. Les finders
`Applicationforms::findVisibleTo` et `Users::findVisibleTo` combinent les
périmètres accordés par OU et refusent les listes sans périmètre applicable.
Les protections de champs, de propriétaire à la création et d’usurpation ne
sont pas modifiées ; seule l’identité effective est évaluée.

Les départements conservent les contrôles existants `departments.read` et
`departments.manage` de leur Policy. L’entrée Web des séquences et leurs
API de lecture vérifient `validationsequences.read` ; les lectures ciblées
restent limitées aux départements directs pour `assigned`, sans extension
aux descendants. Leur administration reste réservée au super-administrateur.
L’entrée Web des menus et les finders de l’écran d’association menus/rôles
reprennent `menus.manage` sur `unscoped`, déjà exigé par `MenuPolicy` ;
l’Admin ordinaire ne reçoit aucun droit implicite.

Vérification du point 4 : syntaxe PHP valide sur les douze fichiers PHP
ajoutés ou modifiés, relecture du diff et `git diff --check` sans erreur.
Aucun test n’a été ajouté ou exécuté, conformément au plan approuvé. Aucun
commit ni aucune application de migration n’ont été réalisés. Le contrôle
fonctionnel et la comparaison des accès restent à effectuer aux points 6
et 7 ; le lien des menus aux permissions dépend de la migration du point 3,
qui reste non appliquée à `daetf` et `daetf2_test`.

### Résultats du point 5 — 18 novembre 2026

La généralisation couvre les utilisateurs, les actions de workflow DAE,
puis les références et fonctions d’administration. Les permissions déjà
intégrées au point 4 et leurs attributions sont conservées. La matrice
complète contient **42 permissions**, **52 périmètres admissibles** et
**76 attributions explicites** :

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

Les nouvelles permissions utilisateurs complètent les contrôles historiques
de rôle Admin, de gérabilité, de périmètre départemental et de champs. Les
contrôleurs Web et API vérifient le droit d’association seulement lorsque
l’ensemble des départements change et capturent les anciennes associations
avant le marshalling. L’usurpation reste super-administrateur uniquement,
sans cascade, et utilise l’identité effective.

Les cinq actions de workflow DAE vérifient chacune leur permission
`unscoped`, sans enlever les contrôles de créateur, de visibilité, de rôle,
de contrat, d’étape ou d’état. Le vote par procuration vérifie
`applicationforms.override_validation` dans la Policy, dans l’API et dans le
service qui réalise le vote. Les indicateurs d’override et de relance des
cycles bloqués sont calculés indépendamment.

Les Policies des références communes, CGR, rôles, autorisations de champs,
paramètres globaux et commentaires prédéfinis utilisent les permissions par
action, avec refus sans attribution. Elles préservent le contournement du
super-administrateur, la suppression interdite des références/rôles socles
et les protections des CGR actifs/système. L’association CGR conserve sa
règle spécialisée. `menus.manage`, la gestion des circuits spécialisés et
les contrôles des gestionnaires locaux restent inchangés. Aucune attribution
ordinaire d’administration, d’usurpation, de menu ou de lecture des séquences
n’est créée.

Les séquences disposent de méthodes de Policy distinctes pour la lecture,
la création, la modification et la suppression. L’API utilise `add` pour
affecter, `edit` pour modifier l’ordre ou le délai, et `delete` pour retirer.
Chaque département ciblé reste contrôlé après les filtres existants et
l’expansion historique ; `assigned` n’accorde aucun descendant implicitement.
Un éventuel ancien scope `all` sur une mutation ne contourne pas ces
contrôles. Le chargement des rôles actifs lors d’une affectation ne dépend
plus du finder réservé aux accès des menus. L’API du paramétrage global
sépare aussi les permissions GET de lecture et POST de modification.

La nouvelle migration `20261118150000_GeneralizeDomainPermissions.php` est
séparée de `ResumeGenericPermissions`, laissée intacte. Elle complète les
permissions, scopes et attributions manquants de façon idempotente, sans
écraser ni supprimer de ligne existante. Les écarts de libellés, de périmètres
et d’attributions sont signalés, y compris toute attribution ordinaire là où
la matrice n’en prévoit aucune. Son `down()` conserve toutes les données et
les migrations antérieures. `PermissionsFixture`, `PermissionScopesFixture`
et `RolePermissionsFixture` portent la matrice complète sans attribuer
`validationsequences.read`.

Vérifications : **125 tests ciblés de Policies et services, 639 assertions,
réussis** avec `phpunit.unit.xml` après les échecs RED attendus ; syntaxe PHP
valide sur les **53 fichiers PHP** ajoutés ou modifiés du checkout ;
`git diff --check` sans erreur. Les scénarios API ajoutés couvrent les refus
par action et scope, les mutations de départements, le workflow DAE, les
séquences et les lectures/écritures de paramètres ; ceux qui utilisent les
fixtures ne sont pas exécutés, car leur bootstrap appliquerait les migrations.
`make test.all` n’a pas été lancé. **Aucune migration n’a été appliquée à
`daetf2_test` ni à `daetf` pendant ce point.** Le point 5 est vérifié au niveau
unitaire ; l’application et les contrôles sur base restent au point 6, et
la comparaison fonctionnelle ainsi que la clôture restent au point 7. Ces
deux étapes restent ouvertes.

## Décisions à préparer

- Appliquer l'ADR 0069 accepté avant la migration, en conservant le modèle de données, les règles de priorité et le cycle d'administration des permissions qu'il définit.
- Compléter les tables génériques `permissions`, `permission_scopes` et `role_permissions`, avec une permission normalisée et une affectation unique par rôle, permission et périmètre.
- Documenter les valeurs `self`, `assigned`, `all` et `unscoped`, ainsi que la règle de refus par défaut.
- Documenter explicitement que `assigned` couvre uniquement les départements directs en V1 ; le calcul des descendants et les droits spécifiques à un utilisateur sont différés.
- Créer un `MenuAccessService` ou des custom finders spécialisés pour résoudre la visibilité depuis la permission référencée par le menu et la Policy, en conservant `role_menus` pendant la comparaison de transition.
- Faire dépendre les Policies de capacités nommées telles que `menus.manage`, au lieu de `role_id = 1`.
- Maintenir le contrôle côté serveur : la disparition d'un lien de navigation ne suffit pas à interdire l'accès direct à son URL ou à son API.
- Garantir que les créations de DAE fixent le propriétaire côté serveur et que les lectures, modifications et suppressions appliquent le périmètre effectif.

## Critères d'acceptation

- Le schéma et l'ADR définissent clairement la différence entre accès métier et permission d'administration.
- L'entrée dans une page et la visibilité de son menu reposent sur la même permission et la Policy ; `role_menus` n'est retiré qu'après comparaison validée des droits, avec le traitement explicite de `department_id`.
- Les permissions sont définies génériquement par ressource, action et périmètre, sans logique spécifique au rôle `demandeur` dans les contrôleurs.
- Les périmètres `self`, `assigned`, `all` et `unscoped` sont documentés et testés ; `assigned` est résolu depuis les affectations organisationnelles effectives de l'opérateur.
- La V1 ne dépend ni d'une hiérarchie de départements calculée à la volée ni d'une affectation de permission propre à chaque utilisateur.
- Le rôle `demandeur` dispose uniquement du CRUD `self` sur les DAE ; la création impose le propriétaire connecté.
- Un opérateur sans permission requise ne voit pas le lien correspondant et ne peut pas atteindre l'URL ou l'API directement, sous réserve du contournement explicite du super-administrateur.
- `menus.manage` protège `/menus`, ses actions d'écriture et l'écran d'attribution rôles-menus ; l'Admin ordinaire en reste exclu conformément au ticket 0006, et l'exception super-administrateur est conservée.
- Le rôle `Roles.id = 1` n'obtient aucun droit implicite : chaque accès et permission est explicitement attribué.
- L'absence d'une permission ou d'un périmètre accordé vaut refus, y compris par accès direct à l'API.
- Le contournement explicite des permissions et périmètres par le super-administrateur est documenté et testé ; les invariants métier, le workflow et les protections d'usurpation restent applicables. En usurpation, seule l'identité effective est évaluée.
- Des tests unitaires couvrent la résolution des permissions et des accès aux menus ; des tests d'intégration couvrent les finders ; des tests HTTP couvrent visibilité, refus d'URL, redirection et Flash.
- Les tests couvrent au minimum un demandeur, un opérateur `assigned`, un accès `all` explicite en lecture et un accès hors périmètre.
- Les scénarios d'acceptation couvrent le CRUD DAE `self` du Demandeur, l'accès `assigned`, le refus hors périmètre, un accès `all` accordé explicitement en lecture et le refus de `menus.manage` à l'Admin ordinaire.
- Les cas `assigned` avec descendants et les différences de périmètre entre utilisateurs de même rôle sont identifiés comme une évolution ultérieure, avec une solution générique documentée.
- Les données existantes reçoivent une migration de reprise explicite, répétable, réversible et vérifiée : lignes préservées, périmètres admissibles respectés, liens menu migrés ou signalés comme exceptions. Les comptes et périmètres repris et les écarts avant/après sont documentés.
- Le catalogue Web reste en lecture seule ; l'attribution et le retrait exceptionnels passent par l'API protégée existante et leurs refus sont vérifiés en HTTP/API.
- La suite complète reste verte avec `make test.all`.

## Motivations

- **Évolutivité :** accorder un écran à un rôle devient une modification de données, non une livraison de code.
- **Sécurité :** les droits sont limités au besoin réel et vérifiés à la fois dans l'IHM et côté serveur.
- **Lisibilité :** les administrateurs fonctionnels voient les associations de menus, tandis que les capacités techniques restent séparées et auditables.
- **Maintenabilité :** les contrôleurs, API, navigation et Policies réutilisent la même décision centralisée ; aucune liste de rôles n'est dupliquée dans le code.

## Références

- [RoleMenusTable](../../src/Model/Table/RoleMenusTable.php)
- [MenuPolicy](../../src/Policy/MenuPolicy.php)
- [ApplicationformPolicy](../../src/Policy/ApplicationformPolicy.php)
- [UserDepartmentsTable](../../src/Model/Table/UserDepartmentsTable.php)
- [Ticket 0006 — Protéger l'accès URL à l'administration des menus](0006-proteger-lacces-url-a-ladministration-des-menus.md)
- [ADR 0069 — Habilitations par rôles et périmètres](../adr/0069-habilitations-par-roles-et-perimetres.md)
- [ADR 0041 — Ségrégation des données via Custom Finders](../adr/0041-segregation-donnees-model-custom-finders.md)
- [ADR 0053 — Stratégie de tests et MySQL dédié à l’intégration](../adr/0053-strategie-tests-unitaires-et-integration.md)
