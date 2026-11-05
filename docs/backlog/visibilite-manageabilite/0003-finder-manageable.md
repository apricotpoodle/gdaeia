# 0003 — Ajouter `findManageableTo()`

**Statut :** Terminé

**Dépendance :** 0002

## Objectif

Implémenter un finder `UsersTable::findManageableTo()` distinct de
`findVisibleTo()`.

## Règle

Le finder retourne les utilisateurs visibles dont le périmètre explicite ne
contient aucun département ancêtre strict d'une racine de l'opérateur.

## Critères d'acceptation

- Un utilisateur local est gérable.
- Un utilisateur associé à un département parent est visible mais non
  gérable.
- Un utilisateur d'une branche indépendante reste non visible.
- Le superuser conserve son périmètre global.
- Les résultats sont dédoublonnés et compatibles avec la pagination distante.

## Vérifications

- Tests d'intégration de `UsersTable`.
- Tests des périmètres multi-branches.

## Réalisation

- [x] Ajouter `UsersTable::findManageableTo()` en s'appuyant sur
  `findVisibleTo()`.
- [x] Résoudre les racines explicites et exclure les utilisateurs associés à
  un ancêtre strict d'une racine.
- [x] Préserver le périmètre global du superuser, le dédoublonnage et la
  pagination distante.
- [x] Couvrir les utilisateurs locaux, les ancêtres, les branches
  indépendantes, les périmètres vides et les superusers.
- [x] Exécuter `make test.integration` sur `daetf2_test` : 121 tests réussis.

