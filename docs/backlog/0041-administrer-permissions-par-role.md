# 0041 — Administrer les permissions par rôle

**Statut :** Prêt

**Priorité :** Haute

**Dépendance :** Socle générique des tickets 0007 et 0021 livré.

## Contexte

Le catalogue des permissions, les périmètres admissibles et les attributions
aux rôles sont définis par l'ADR 0069. Le vertical des départements permet déjà
d'auditer les permissions et leurs associations, mais l'administration
générique des attributions par rôle doit exposer le catalogue complet, y
compris les permissions qui ne sont attribuées à aucun rôle.

Dans la matrice de référence de l'ADR 0069, le rôle d'identifiant 2 est décrit
comme « Demandeur ». L'interface doit toutefois lire l'identifiant et le
libellé des rôles depuis la base, sans coder cette valeur en dur.

La grille présente les droits attribués au rôle choisi. Elle ne calcule ni ne
simule les droits effectifs d'une personne sur un objet, lesquels dépendent
aussi de son identité, de son périmètre et des règles métier.

## Objectif

Permettre au super-administrateur de consulter et de gérer les attributions de
permissions et de périmètres admissibles d'un rôle, avec un contrôle serveur et
une représentation fidèle du catalogue existant.

Le catalogue et la matrice initiale restent inchangés : ce ticket ne modifie
pas les attributions créées par les migrations et ne prévoit aucune attribution
initiale au rôle 2. Aucun changement de schéma n'est prévu.

## Travail attendu

Les cinq étapes sont strictement ordonnées. Une étape doit être terminée et
vérifiée avant de commencer la suivante.

1. [x] Vérifier le contrat existant et inventorier les rôles, permissions,
   périmètres admissibles, attributions et URLs de menu associées, sans
   modifier les attributions initiales.
2. [x] Étendre l'API de lecture pour exposer, selon le rôle choisi, le
   catalogue complet, les périmètres admissibles, les attributions du rôle et
   les URLs associées.
3. [ ] Afficher le sélecteur de rôle et une grille Tabulator distante qui
   présente aussi les permissions sans attribution.
4. [ ] Permettre l'ajout et le retrait des seules attributions admissibles,
   avec autorisation et validation côté serveur, et des erreurs conformes au
   contrat API.
5. [ ] Compléter la documentation, les tests et les vérifications adaptées au
   périmètre réellement modifié.

### Résultats du point 1 — 20 novembre 2026

L'inventaire a été réalisé en lecture seule sur la base locale `daetf`. La
base configurée et la base renvoyée par MySQL ont toutes deux été vérifiées
avant les requêtes d'inventaire. Les agrégats relevés au début et à la fin
sont identiques : **9 rôles, 42 permissions, 52 périmètres admissibles,
76 attributions, 33 menus et 28 associations `role_menus`**.

La matrice des permissions, des périmètres et des attributions concorde avec
l'ADR 0069 et les migrations
`20261118140000_ResumeGenericPermissions` et
`20261118150000_GeneralizeDomainPermissions`. Les rôles 1 à 7 sont ceux de la
matrice ; les rôles 90 et 91 existent aussi dans la base et ne portent aucune
attribution. Aucun écart de permission, de périmètre ou d'attribution n'a été
relevé.

#### Rôles et attributions

| Rôle | Code | Libellé | Attributions |
|---:|---|---|---:|
| 1 | `adm` | Admin | 22 |
| 2 | `dem` | Demandeur | 8 |
| 3 | `vdp` | 1 Valideur de Pôle | 9 |
| 4 | `vrrh` | 2 Valideur Responsable Ressources Humaines | 9 |
| 5 | `vdrh` | 3 Valideur Direction Ressources Humaines | 9 |
| 6 | `vcg` | 4 Valideur Contrôle de Gestion | 9 |
| 7 | `dir` | 5 Valideur Directoire | 10 |
| 90 | `DIRECTION` | Direction | 0 |
| 91 | `VSG` | 2 Secr. Gén. | 0 |

Le catalogue et ses périmètres admissibles sont les suivants. `—` indique
qu'aucun rôle ne reçoit cette permission dans la matrice.

| Permission | Libellé | Périmètres admissibles |
|---|---|---|
| `applicationforms.archive` | Archiver les demandes | `unscoped` |
| `applicationforms.create` | Créer une demande | `self` |
| `applicationforms.delete` | Supprimer les demandes | `self` |
| `applicationforms.launch_validation` | Lancer la validation des demandes | `unscoped` |
| `applicationforms.override_validation` | Outrepasser la validation des demandes | `unscoped` |
| `applicationforms.read` | Consulter les demandes | `self`, `assigned`, `all` |
| `applicationforms.remind_validation` | Relancer la validation des demandes | `unscoped` |
| `applicationforms.reset_validation` | Réinitialiser la validation des demandes | `unscoped` |
| `applicationforms.update` | Modifier les demandes | `self`, `assigned`, `all` |
| `applicationforms.vote` | Voter sur les demandes | `unscoped` |
| `departments.manage` | Administrer les départements | `assigned`, `all` |
| `departments.read` | Consulter les départements | `assigned`, `all` |
| `fieldauthorizations.create` | Créer les autorisations de champs | `unscoped` |
| `fieldauthorizations.delete` | Supprimer les autorisations de champs | `unscoped` |
| `fieldauthorizations.read` | Consulter les autorisations de champs | `unscoped` |
| `fieldauthorizations.update` | Modifier les autorisations de champs | `unscoped` |
| `menus.manage` | Administrer les menus | `unscoped` |
| `references.create` | Créer les références | `unscoped` |
| `references.delete` | Supprimer les références | `unscoped` |
| `references.read` | Consulter les références | `unscoped` |
| `references.update` | Modifier les références | `unscoped` |
| `roles.create` | Créer les rôles | `unscoped` |
| `roles.delete` | Supprimer les rôles | `unscoped` |
| `roles.read` | Consulter les rôles | `unscoped` |
| `roles.update` | Modifier les rôles | `unscoped` |
| `users.associate_departments` | Associer les départements des utilisateurs | `assigned` |
| `users.change_role` | Changer les rôles des utilisateurs | `unscoped` |
| `users.create` | Créer les utilisateurs | `unscoped` |
| `users.delete` | Supprimer les utilisateurs | `unscoped` |
| `users.impersonate` | Usurper une identité | `unscoped` |
| `users.read` | Consulter les utilisateurs | `self`, `assigned`, `all` |
| `users.update` | Modifier les utilisateurs | `self`, `assigned` |
| `validationcommenttemplates.create` | Créer les commentaires prédéfinis | `unscoped` |
| `validationcommenttemplates.delete` | Supprimer les commentaires prédéfinis | `unscoped` |
| `validationcommenttemplates.read` | Consulter les commentaires prédéfinis | `unscoped` |
| `validationcommenttemplates.update` | Modifier les commentaires prédéfinis | `unscoped` |
| `validationsequences.create` | Créer les séquences de validation | `assigned` |
| `validationsequences.delete` | Supprimer les séquences de validation | `assigned` |
| `validationsequences.read` | Consulter les séquences de validation | `assigned`, `all` |
| `validationsequences.update` | Modifier les séquences de validation | `assigned` |
| `workflowsettings.read` | Consulter le paramétrage global du workflow | `unscoped` |
| `workflowsettings.update` | Modifier le paramétrage global du workflow | `unscoped` |

Les attributions effectives par rôle (code permission suivi du périmètre)
sont :

| Rôle | Attributions |
|---|---|
| 1 — Admin | `applicationforms.archive:unscoped`, `create:self`, `delete:self`, `launch_validation:unscoped`, `override_validation:unscoped`, `read:self` et `assigned`, `remind_validation:unscoped`, `reset_validation:unscoped`, `update:self` et `assigned`, `vote:unscoped`; `departments.manage:assigned`, `departments.read:assigned`; `users.associate_departments:assigned`, `change_role:unscoped`, `create:unscoped`, `delete:unscoped`, `read:self` et `assigned`, `update:self` et `assigned` |
| 2 — Demandeur | `applicationforms.create:self`, `delete:self`, `launch_validation:unscoped`, `read:self`, `update:self`, `vote:unscoped`; `users.read:self`, `users.update:self` |
| 3 à 6 — Valideurs | Chacun : `applicationforms.delete:self`, `launch_validation:unscoped`, `read:self` et `assigned`, `update:self` et `assigned`, `vote:unscoped`; `users.read:self`, `users.update:self` |
| 7 — Valideur Directoire | Mêmes attributions que les rôles 3 à 6, avec `applicationforms.archive:unscoped` |
| 90 — Direction, 91 — 2 Secr. Gén. | Aucune |

#### Menus, URLs et permissions requises

| ID | Option | URL | Permission requise |
|---:|---|---|---|
| 1 | Administration | — | — |
| 2 | nouvelle option menu | `menus/add` | `menus.manage` |
| 3 | Menus | `menus` | `menus.manage` |
| 4 | Utilisateurs | `users` | `users.read` |
| 5 | Utilisateur ajout | `users/add` | — |
| 6 | Association Service Utilisateur | `UserDepartments/assocDepartmentUsers` | — |
| 7 | Saisie Demande | `applicationforms/add` | `applicationforms.create` |
| 8 | Association Rôle Menu | `Menus/role_access` | `menus.manage` |
| 9 | Associations | `#` | — |
| 10 | Association Utilisateur Service | `UserDepartments/assocUserDepartments` | — |
| 11 | Références | `#` | — |
| 12 | Entités Services | `Departments` | `departments.read` |
| 13 | Utilisateurs | `Users` | `users.read` |
| 14 | Rôles | `Roles` | — |
| 15 | Association Séquence Department | `Validationsequences` | `validationsequences.read` |
| 16 | Types de Contrat | `Contracttypes` | — |
| 17 | Types Contrat UI dynamique Beta test | `Contracttypes/indexDyna` | — |
| 18 | Assignation Responsable Service | `Departments/assignManagers` | `departments.manage` |
| 19 | Stratégies CGR | `cgr-strategies` | — |
| 20 | Association Code CGR Service | `departments/assignServices` | `departments.manage` |
| 21 | Envoi email quotidien (préférez la CLI) | `Tools/triggerDailyDigest` | — |
| 22 | CGR Codes | `cgr-codes` | — |
| 23 | Matrice des droits ApplicationForm | `field-authorizations` | — |
| 24 | Association massive user department | `users/bulk-departments` | — |
| 25 | Validation séquences et workflow | `Validationsequences` | `validationsequences.read` |
| 26 | Types de contrats | `contracttypes` | — |
| 27 | Motifs de recrutement | `hiringreasons` | — |
| 28 | Catégories professionnelles | `professionalcategories` | — |
| 29 | Temps de travail | `worktimes` | — |
| 30 | Périodicités | `periods` | — |
| 31 | Caractéristiques budgétaires | `budgetfeatures` | — |
| 32 | Réponses Oui / Non | `yesnos` | — |
| 33 | Paramétrage global du cycle | `workflow-settings` | — |

Les 22 options sans permission requise sont les IDs 1, 5, 6, 9, 10, 11, 14,
16, 17, 19, 21, 22, 23, 24 et 26 à 33. Elles sont signalées sans être
corrigées.

#### Associations historiques `role_menus`

Ces associations de navigation sont inventoriées séparément des attributions
`role_permissions`. Les 28 lignes présentes sont :

| Rôle | Menus associés (`menu_id`) | Nombre |
|---|---|---:|
| 1 — Admin | 6 à 16, 18, 20, 22, 24, 26 à 32 | 22 |
| 2 — Demandeur | 7 | 1 |
| 3 — 1 Valideur de Pôle | 7 | 1 |
| 4 — 2 Valideur Responsable Ressources Humaines | 7 | 1 |
| 5 — 3 Valideur Direction Ressources Humaines | 7 | 1 |
| 6 — 4 Valideur Contrôle de Gestion | 7 | 1 |
| 7 — 5 Valideur Directoire | 7 | 1 |

Les 28 lignes ont `department_id = NULL`. Les rôles 90 et 91 n'ont aucune
association `role_menus`. Aucun écart n'est corrigé : les tables, le schéma et
les attributions restent inchangés. Aucun renseignement personnel n'est
consigné.

## Critères d'acceptation

1. L'écran Web et les endpoints concernés sont réservés au super-administrateur
   par les Policies ; un accès direct ou une requête forgée est également
   refusé.
2. Le sélecteur lit les identifiants et libellés des rôles depuis la base.
3. Pour le rôle sélectionné, la grille expose toutes les permissions, y
   compris celles qui n'ont aucune attribution, leurs périmètres admissibles,
   les attributions du rôle et les URLs de menu associées lorsqu'elles
   existent.
4. La grille décrit les attributions du rôle et ne présente pas les droits
   effectifs d'une personne sur un objet.
5. Une attribution ne peut être créée qu'avec une permission du catalogue et
   un périmètre admissible pour cette permission. Les périmètres invalides sont
   refusés côté serveur avec une erreur API de validation appropriée.
6. Le super-administrateur peut ajouter et retirer une attribution existante
   par les mutations prévues ; les autres identités ne peuvent pas les
   effectuer.
7. La consultation et la gestion n'ajoutent, ne retirent et ne remplacent
   aucune attribution initiale ; aucune attribution initiale au rôle 2 n'est
   créée et aucun changement de schéma n'est introduit.
8. L'API conserve le contrat d'erreur `{success, message, errors}` et utilise
   HTTP 422 pour les erreurs de validation pertinentes.

## Vérifications

- Ajouter des tests couvrant les accès Web et API autorisés/refusés, la lecture
  du catalogue incluant les permissions sans attribution, les mutations
  autorisées et les périmètres invalides.
- Exécuter `make test.unit` et `make test.api`.
- Exécuter `make test.integration` si les requêtes ORM changent.
- Exécuter `make test.style` si les tests sont modifiés.
- Exécuter `make cs.check` et `make stan` si du PHP est modifié.
- Vérifier que les attributions initiales et le schéma restent inchangés.

## Références

- [ADR 0069 — Habilitations par rôles et périmètres](../adr/0069-habilitations-par-roles-et-perimetres.md)
- [ADR 0065 — ACL automatique des mutations Web et API](../adr/0065-acl-automatique-des-mutations-web-api.md)
- [ADR 0004 — API Tabulator et patrons de conception](../adr/0004-api-tabulator-et-design-patterns.md)
- [Ticket 0007 — Séparer les accès aux menus des permissions d'administration](0007-separer-acces-menus-et-permissions-administration.md)
- [Ticket 0021 — Implémenter les habilitations génériques](0021-implementer-les-habilitations-generiques.md)
