# 0009 — Recette transversale et clôture

**Statut :** Terminé

**Dépendance :** 0008

## Objectif

Vérifier la cohérence finale entre finders, Policies, services, contrôleurs,
API, grilles et rôles.

## Scénarios de recette

- [x] superuser ;
- [x] administrateur local et user de son périmètre ;
- [x] user visible associé à un département parent ;
- [x] user d'une branche indépendante ;
- [x] périmètre multi-branches ;
- [x] changement de rôle fonctionnel ;
- [x] refus d'attribution de `ADMIN` par un administrateur local ;
- [x] attribution de `ADMIN` par un superuser ;
- [x] accès direct Web et API aux mutations.

## Vérifications attendues

- [x] `make test.unit` — 103 tests réussis ;
- [x] `make test.integration` — 121 tests ORM réussis ;
- [x] `make test.api` — 177 tests HTTP/API réussis ;
- [x] `make test.style` — vérification réussie ;
- [x] `make cs.check` — aucune erreur ;
- [x] `make stan` — aucune erreur ;
- [x] `make test.all` — 477 tests réussis ;
- [x] vérification des associations ORM selon l'ADR 0010 : le bootstrap
  CakePHP a compilé sans exception les associations de `Users`, `Departments`,
  `UserDepartments`, `DepartmentManagers` et `Roles`.

## Clôture

- [x] Mettre à jour les README locaux et la synthèse du backlog.
- [x] Passer les sous-tickets et le ticket parent à `Terminé` après validation.
- [x] Confirmer que l'ADR 0073 est `Accepté`.

## Résultat de la recette

La recette transversale du 5 novembre 2026 confirme la cohérence entre les
finders, Policies, services, contrôleurs, API, grilles et rôles. Les
mutations hors périmètre sont refusées côté serveur et les utilisateurs
visibles mais non gérables restent consultables en lecture seule.

