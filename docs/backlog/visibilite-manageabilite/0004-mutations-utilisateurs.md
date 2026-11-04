# 0004 — Protéger les mutations des utilisateurs

**Statut :** À planifier

**Dépendance :** 0003

## Objectif

Faire de la gérabilité une condition d'autorisation pour la fiche et les
mutations Web/API des utilisateurs.

## Travail attendu

- Intégrer la règle dans `UserPolicy::canEdit()` et les actions concernées.
- Vérifier la cible juste avant `save()` dans les contrôleurs et services.
- Refuser les URLs et appels API directs hors périmètre.
- Conserver la lecture des utilisateurs visibles non gérables.

## Incompatibilité corrigée

`UserPolicy::canEdit()` autorise actuellement un administrateur sans vérifier
le périmètre de la cible.

## Vérifications

- Tests unitaires de Policy.
- Tests fonctionnels Web et API pour accès autorisé, refusé et contournement
  du formulaire.

