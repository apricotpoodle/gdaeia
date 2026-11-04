# 0003 — Ajouter `findManageableTo()`

**Statut :** À planifier

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

