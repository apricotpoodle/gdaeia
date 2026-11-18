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

- [ ] **1. Inventorier l'existant.** Relever les permissions et règles dans les Policies, finders et routes Web/API ; comparer le comportement au catalogue, aux périmètres et attributions de `daetf` et aux 28 associations `role_menus`. Examiner séparément `department_id` et les destinations partagées ; distinguer les droits déductibles des exceptions à arbitrer.
- [ ] **2. Fixer la matrice des permissions.** Définir pour chaque capacité son code, ses périmètres admissibles et ses attributions aux rôles, conformément à l'ADR 0069. Préserver le refus d'administration des menus pour l'Admin ordinaire du ticket 0006 et l'exception explicite du super-administrateur.
- [ ] **3. Automatiser la reprise des données.** Écrire une migration répétable qui préserve les lignes existantes et complète le catalogue `permissions`, les périmètres admissibles `permission_scopes` et les attributions déterminables `role_permissions`. Ajouter `menus.required_permission_id` et les liens menu déductibles sans ambiguïté ; signaler les autres. Ne pas attribuer `menus.manage` à l'Admin ordinaire.
- [ ] **4. Sécuriser la transition des menus.** Vérifier les mêmes permissions pour la visibilité et l'entrée directe par URL/API, avec les Policies et périmètres applicables. Traiter les rubriques sans destination et les associations non migrables automatiquement. Garder `role_menus` jusqu'à validation de la comparaison des droits avant/après.
- [ ] **5. Généraliser par domaine.** Avancer par volets indépendamment vérifiables : utilisateurs et périmètres ; DAE et règles de workflow ; puis références et autres fonctions d'administration. Conserver les invariants métier et du workflow dans leurs Policies et services, ainsi que les protections de champs et d'usurpation.
- [ ] **6. Compléter la base de développement.** Valider la migration sur `daetf2_test`, vérifier explicitement la connexion cible, puis l'appliquer à `daetf`. Configurer uniquement les attributions exceptionnelles non déductibles avec l'API protégée existante et les consigner ; ne pas utiliser de SQL manuel. La page Web du catalogue reste en lecture seule, sans nouvelle interface de gestion.
- [ ] **7. Vérifier et clôturer.** Comparer les accès avant/après pour les comptes et périmètres repris, documenter les écarts et exceptions validés, puis actualiser les critères d'acceptation et le statut pour clôturer le ticket. Prévoir les vérifications unitaires, d'intégration et HTTP/API ci-dessous, puis `make test.all` sur `daetf2_test`.

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
