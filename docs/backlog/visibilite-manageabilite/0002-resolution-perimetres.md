# 0002 — Résoudre les périmètres explicites

**Statut :** Terminé

**Dépendance :** 0001

## Objectif

Ajouter au modèle les requêtes nécessaires pour récupérer les départements
explicitement associés à un utilisateur et déterminer les ancêtres stricts
avec les colonnes `lft` et `rght`.

## Travail attendu

- Réutiliser `UserDepartmentsTable::findDepartmentsOf()` ou l'étendre sans
  dupliquer la logique.
- Ajouter les requêtes de comparaison des départements actifs.
- Prévoir les périmètres à plusieurs racines et les associations partielles.
- Ne pas inférer les descendants absents de `user_departments`.

## Vérifications

- Tests d'intégration ORM sur les racines, parents, enfants et branches
  indépendantes.
- Vérification des associations ORM selon l'ADR 0010.

## Réalisation

- [x] Réutiliser `UserDepartmentsTable::findDepartmentsOf()` pour retourner
  les associations explicites dédoublonnées.
- [x] Ajouter `DepartmentsTable::findStrictAncestorsOf()` avec comparaison
  stricte des bornes `lft` et `rght`.
- [x] Exclure les départements inactifs et ne pas inférer les descendants
  absents de `user_departments`.
- [x] Couvrir les racines multiples, les branches indépendantes et l'égalité
  des bornes par des tests d'intégration ORM.
- [x] Vérifier la base de test dédiée `daetf2_test` et l'intégrité ORM.

## Vérifications exécutées

- [x] `make test.integration` — 118 tests réussis.

