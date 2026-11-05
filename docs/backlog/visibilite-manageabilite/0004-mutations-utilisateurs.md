# 0004 — Protéger les mutations des utilisateurs

**Statut :** Terminé

**Dépendance :** 0003

## Objectif

Faire de la gérabilité une condition d'autorisation pour la fiche et les
mutations Web/API des utilisateurs.

## Travail attendu

- [x] Intégrer la règle de gérabilité dans `UserPolicy::canEdit()` et
  `canAssociate()`, ainsi que dans les mutations de masse concernées.
- [x] Vérifier la cible juste avant `save()` dans les contrôleurs Web et API.
- [x] Refuser les URLs et appels API directs hors périmètre.
- [x] Conserver la lecture des utilisateurs visibles non gérables.

## Incompatibilité corrigée

`UserPolicy::canEdit()` autorise actuellement un administrateur sans vérifier
le périmètre de la cible.

## Vérifications

- [x] Tests unitaires de Policy.
- [x] Tests fonctionnels Web et API pour accès autorisé, refusé et contournement
  du formulaire.

### Suivi de l'implémentation

- `UserPolicy` consulte `UsersTable::findManageableTo()` pour les mutations
  d'un opérateur local ; le superuser conserve son court-circuit global.
- Les éditions Web et API revérifient l'autorisation après hydratation et juste
  avant l'écriture.
- Les mutations API de masse refusent les cibles visibles mais non gérables.
- La lecture Web d'une cible visible mais non gérable reste autorisée.
- Les règles détaillées des affectations, rôles et responsables restent dans
  les sous-tickets 0005 à 0007.
- Vérifications exécutées : `make test.unit` (97 tests), `make test.api`
  (168 tests), `make test.integration` (121 tests ORM), `make cs.check`,
  `make stan`, `make test.style` et `make test.all` (462 tests).

