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

## Décisions à préparer

- Rédiger et faire accepter un ADR avant la migration, couvrant le modèle de données, les règles de priorité et le cycle d'administration des permissions.
- Définir une table `role_permissions` avec au minimum `role_id` et une permission normalisée, unique par rôle.
- Créer un `MenuAccessService` ou des custom finders spécialisés pour résoudre les accès aux menus à partir de `role_menus`, de la visibilité départementale et du statut de super-administrateur.
- Faire dépendre les Policies de capacités nommées telles que `manage_menus`, au lieu de `role_id = 1`.
- Maintenir le contrôle côté serveur : la disparition d'un lien de navigation ne suffit pas à interdire l'accès direct à son URL ou à son API.

## Critères d'acceptation

- Le schéma et l'ADR définissent clairement la différence entre accès métier et permission d'administration.
- Les accès à une option métier sont calculés depuis `role_menus`, y compris les règles de département déjà applicables.
- Un rôle sans association à un menu ne voit pas le lien correspondant et ne peut pas atteindre l'URL ou l'API directement.
- `manage_menus` est requis pour `/menus`, ses actions d'écriture et l'écran d'attribution rôles-menus.
- Le rôle `Roles.id = 1` n'obtient aucun droit implicite : chaque accès et permission est explicitement attribué.
- Le super-administrateur suit une règle documentée et testée, notamment sur son éventuel contournement des permissions ordinaires.
- Des tests unitaires couvrent la résolution des permissions et des accès aux menus ; des tests d'intégration couvrent les finders ; des tests HTTP couvrent visibilité, refus d'URL, redirection et Flash.
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
- [Ticket 0006 — Protéger l'accès URL à l'administration des menus](0006-proteger-lacces-url-a-ladministration-des-menus.md)
- [ADR 0041 — Ségrégation des données via Custom Finders](../adr/0041-segregation-donnees-model-custom-finders.md)
- [ADR 0053 — Stratégie de tests et MySQL dédié à l’intégration](../adr/0053-strategie-tests-unitaires-et-integration.md)
