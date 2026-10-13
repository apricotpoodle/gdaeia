# 0007 — Séparer les accès aux menus des permissions d'administration

**Statut :** À planifier

**Priorité :** Haute

## Contexte

Les Policies de menus déterminent aujourd'hui plusieurs autorisations au moyen de rôles codés en dur. Cette approche est simple tant que les rôles sont peu nombreux, mais elle confond deux notions distinctes : l'accès à un écran métier et l'administration transversale de l'application.

La table `role_menus` permet déjà d'associer un rôle à une option de menu, avec un éventuel périmètre départemental. Elle est donc le bon support de données pour définir qui peut utiliser un écran métier. En revanche, utiliser cette association pour décider qui peut modifier les menus et les droits créerait une dépendance circulaire : il faut contrôler l'administration du référentiel avant de pouvoir modifier les données qui définissent les accès.

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

`role_menus` peut décrire les accès métier de façon déclarative :

| role_id | menu_id | Effet |
|---:|---:|---|
| 1 | 10 | L'administrateur saisit une demande. |
| 1 | 11 | L'administrateur valide une demande. |
| 2 | 10 | Le gestionnaire saisit une demande. |
| 3 | 10 | L'agent saisit une demande. |

Ajouter `role_id = 3, menu_id = 11` donne ensuite accès à la validation à l'agent, sans modifier une Policy ni déployer l'application.

Une table générique distincte, par exemple `role_permissions`, porte les capacités techniques :

| role_id | permission | Effet |
|---:|---|---|
| 1 | `manage_users` | Administrer les utilisateurs. |
| 1 | `manage_menus` | Administrer les menus et leurs associations. |
| 2 | `manage_applicationforms` | Administrer le référentiel de demandes, si cette capacité existe. |

Le gestionnaire peut ainsi utiliser « Saisie demande » sans jamais pouvoir accéder à `/menus`. L'administrateur technique peut disposer de `manage_menus` sans que cela lui accorde automatiquement tous les écrans métier. Cette séparation applique le principe du moindre privilège.

## Objectif

Faire de `role_menus` la source de vérité des accès aux écrans métier et introduire des permissions de rôle explicites pour les fonctions transversales d'administration. Les Policies doivent conserver leur rôle de frontière de sécurité, mais déléguer la décision aux données centralisées plutôt que comparer des identifiants de rôles.

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

role_permissions
----------------
id, role_id, permission_id, scope_type
```

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

`assigned` est préféré à `department` : le premier décrit la règle d'accès
fondée sur les affectations de l'opérateur, tandis que le second ne décrit
que la nature d'une colonne métier. `all` ne signifie pas automatiquement
« superadministrateur » : il s'agit d'un périmètre explicite qui peut être
accordé en lecture seule à un auditeur ou à un compte de reporting. L'absence
de permission vaut refus.

Les menus et les permissions restent deux décisions distinctes :

- `role_menus` détermine uniquement si une option de navigation est visible ;
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

## Décisions à préparer

- Rédiger et faire accepter un ADR avant la migration, couvrant le modèle de données, les règles de priorité et le cycle d'administration des permissions.
- Définir les tables génériques `permissions` et `role_permissions`, avec une permission normalisée et une affectation unique par rôle, permission et périmètre.
- Documenter les valeurs `self`, `assigned` et `all`, ainsi que la règle de refus par défaut.
- Documenter explicitement que `assigned` couvre uniquement les départements directs en V1 ; le calcul des descendants et les droits spécifiques à un utilisateur sont différés.
- Créer un `MenuAccessService` ou des custom finders spécialisés pour résoudre les accès aux menus à partir de `role_menus`, de la visibilité départementale et du statut de super-administrateur.
- Faire dépendre les Policies de capacités nommées telles que `manage_menus`, au lieu de `role_id = 1`.
- Maintenir le contrôle côté serveur : la disparition d'un lien de navigation ne suffit pas à interdire l'accès direct à son URL ou à son API.
- Garantir que les créations de DAE fixent le propriétaire côté serveur et que les lectures, modifications et suppressions appliquent le périmètre effectif.

## Critères d'acceptation

- Le schéma et l'ADR définissent clairement la différence entre accès métier et permission d'administration.
- Les accès à une option métier sont calculés depuis `role_menus`, y compris les règles de département déjà applicables.
- Les permissions sont définies génériquement par ressource, action et périmètre, sans logique spécifique au rôle `demandeur` dans les contrôleurs.
- Les périmètres `self`, `assigned` et `all` sont documentés et testés ; `assigned` est résolu depuis les affectations organisationnelles effectives de l'opérateur.
- La V1 ne dépend ni d'une hiérarchie de départements calculée à la volée ni d'une affectation de permission propre à chaque utilisateur.
- Le rôle `demandeur` dispose uniquement du CRUD `self` sur les DAE ; la création impose le propriétaire connecté.
- Un rôle sans association à un menu ne voit pas le lien correspondant et ne peut pas atteindre l'URL ou l'API directement.
- `manage_menus` est requis pour `/menus`, ses actions d'écriture et l'écran d'attribution rôles-menus.
- Le rôle `Roles.id = 1` n'obtient aucun droit implicite : chaque accès et permission est explicitement attribué.
- L'absence d'une permission ou d'un périmètre accordé vaut refus, y compris par accès direct à l'API.
- Le super-administrateur suit une règle documentée et testée, notamment sur son éventuel contournement des permissions ordinaires.
- Des tests unitaires couvrent la résolution des permissions et des accès aux menus ; des tests d'intégration couvrent les finders ; des tests HTTP couvrent visibilité, refus d'URL, redirection et Flash.
- Les tests couvrent au minimum un demandeur, un opérateur `assigned`, un accès `all` explicite en lecture et un accès hors périmètre.
- Les cas `assigned` avec descendants et les différences de périmètre entre utilisateurs de même rôle sont identifiés comme une évolution ultérieure, avec une solution générique documentée.
- Les données existantes reçoivent une migration de reprise explicite, réversible et vérifiée.
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
- [ADR 0041 — Ségrégation des données via Custom Finders](../adr/0041-segregation-donnees-model-custom-finders.md)
- [ADR 0053 — Stratégie de tests et MySQL dédié à l’intégration](../adr/0053-strategie-tests-unitaires-et-integration.md)
