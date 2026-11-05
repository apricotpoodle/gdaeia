# 0025 — Séparer la visibilité et la gérabilité des utilisateurs

**Statut :** Terminé

**Priorité :** Haute — sécurité des mutations et habilitations

## Nature du ticket

Ce ticket est le ticket parent du sous-workflow
[`visibilite-manageabilite/`](visibilite-manageabilite/). Les sous-tickets sont
la source de vérité opérationnelle pour l'ordre d'exécution, les dépendances
et les critères de vérification.

Le manifeste est disponible dans
[`visibilite-manageabilite/manifest.json`](visibilite-manageabilite/manifest.json).

## Contexte

`UsersTable::findVisibleTo()` filtre les utilisateurs visibles selon les
associations explicites de `user_departments`. Cette visibilité ne signifie
pas qu'un opérateur puisse modifier l'utilisateur. Un administrateur local
peut voir un administrateur dont le périmètre comprend un département parent
de sa propre racine, notamment pour les besoins des cycles de validation,
mais il ne doit pas pouvoir muter cet administrateur.

La distinction doit être appliquée aux utilisateurs, aux affectations de
départements et aux responsables associés depuis une fiche département. Elle
doit rester valable en cas d'accès direct à une URL ou d'appel API fabriqué
manuellement.

## Décisions fonctionnelles

- `findVisibleTo()` conserve son rôle de filtrage des utilisateurs lisibles.
- `findManageableTo()` retourne les utilisateurs visibles dont le périmètre
  ne contient aucun département ancêtre strict d'une racine explicite de
  l'opérateur.
- Les associations analysées sont exclusivement celles présentes dans
  `user_departments` ; aucun droit implicite ne doit être déduit d'un parent.
- Un utilisateur visible mais non gérable peut rester affiché en lecture
  seule.
- La gérabilité couvre la modification des informations utilisateur, les
  affectations `user_departments` et les associations `department_managers`.
- Un administrateur local peut modifier le rôle d'un utilisateur gérable,
  mais seul un superuser peut attribuer `ADMIN` ou modifier `issuperuser`.

## Incompatibilités actuelles à traiter

1. `UserPolicy::canEdit()` autorise actuellement un administrateur sans
   vérifier la gérabilité de la cible.
2. `UserPolicy::canAssociate()` ne contrôle pas le périmètre de la cible.
3. `DepartmentsController::addManager()` et la liste des responsables
   disponibles utilisent `findVisibleTo()` au lieu d'une règle de
   gérabilité.
4. La dissociation dispose d'un contrôle hors périmètre partiel, qui doit être
   aligné sur la nouvelle règle et conservé côté serveur.
5. `DepartmentPolicy::canManageDepartments()` considère tout `ADMIN` comme
   administrateur global. Tant que cette Policy n'est pas rendue sensible au
   périmètre, le rôle `ADMIN` doit rester réservé au superuser.
6. Les affectations `user_departments` doivent être vérifiées individuellement
   avant l'écriture ; filtrer une grille ne suffit pas à sécuriser une API.

## Critères d'acceptation du ticket parent

1. Les règles de visibilité et de gérabilité sont documentées et validées.
2. Les utilisateurs visibles mais non gérables sont identifiables.
3. Les fiches Web et les endpoints API refusent les mutations hors périmètre.
4. Les affectations `user_departments` et `department_managers` sont
   protégées côté serveur.
5. Un administrateur local ne peut pas attribuer `ADMIN` ou `issuperuser`.
6. Les cas de périmètres simples, parents, branches indépendantes et
   multi-branches sont testés.
7. Le sous-workflow est terminé et ses vérifications sont documentées.

## Références

- [ADR 0041 — Ségrégation des données par custom finders](../adr/0041-segregation-donnees-model-custom-finders.md)
- [ADR 0050 — Sémantique des périmètres hiérarchiques utilisateur](../adr/0050-semantique-perimetres-hierarchiques-utilisateur.md)
- [ADR 0065 — ACL automatique des mutations Web et API](../adr/0065-acl-automatique-des-mutations-web-api.md)
- [ADR 0069 — Habilitations par rôles et périmètres](../adr/0069-habilitations-par-roles-et-perimetres.md)
- [ADR 0073 — Distinction entre visibilité et gérabilité](../adr/0073-visibilite-et-manageabilite-des-utilisateurs.md)

