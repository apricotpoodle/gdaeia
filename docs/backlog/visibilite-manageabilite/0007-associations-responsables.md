# 0007 — Sécuriser les associations de responsables

**Statut :** Terminé

**Dépendances :** 0003 et 0004

## Objectif

Appliquer la distinction visible/gérable dans l'onglet « Responsables »
et dans les routes d'association des départements.

## Travail attendu

- [x] Utiliser `findManageableTo()` pour les responsables disponibles.
- [x] Revalider la gérabilité dans `addManager()`.
- [x] Utiliser la même règle pour `removeManager()`.
- [x] Conserver les responsables existants hors périmètre en lecture seule.
- [x] Aligner `can_dissociate` et le message d'erreur sur la Policy serveur.

## Incompatibilités à traiter

Les listes et `DepartmentsController::addManager()` utilisent actuellement
`findVisibleTo()`. La dissociation possède déjà un contrôle partiel, qui doit
être harmonisé avec la nouvelle règle.

## Vérifications

- [x] Tests fonctionnels des listes disponibles et associées.
- [x] Tests d'ajout, de dissociation, de refus et de contournement API.

## Réalisation

Les listes de responsables disponibles utilisent désormais `findManageableTo()`.
Les routes d'ajout et de dissociation revérifient la gérabilité côté serveur,
tandis que les responsables déjà associés hors périmètre restent visibles en
lecture seule. Les droits `can_dissociate` et le message de lecture seule
reposent sur la même règle que les mutations.

Vérifications exécutées : `make test.api` (176 tests, 620 assertions).

