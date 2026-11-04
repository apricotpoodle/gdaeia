# 0007 — Sécuriser les associations de responsables

**Statut :** À planifier

**Dépendances :** 0003 et 0004

## Objectif

Appliquer la distinction visible/gérable dans l'onglet « Responsables »
et dans les routes d'association des départements.

## Travail attendu

- Utiliser `findManageableTo()` pour les responsables disponibles.
- Revalider la gérabilité dans `addManager()`.
- Utiliser la même règle pour `removeManager()`.
- Conserver les responsables existants hors périmètre en lecture seule.
- Aligner `can_dissociate` et le message d'erreur sur la Policy serveur.

## Incompatibilités à traiter

Les listes et `DepartmentsController::addManager()` utilisent actuellement
`findVisibleTo()`. La dissociation possède déjà un contrôle partiel, qui doit
être harmonisé avec la nouvelle règle.

## Vérifications

- Tests fonctionnels des listes disponibles et associées.
- Tests d'ajout, de dissociation, de refus et de contournement API.

