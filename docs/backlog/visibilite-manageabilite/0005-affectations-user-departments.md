# 0005 — Sécuriser les affectations `user_departments`

**Statut :** À planifier

**Dépendances :** 0003 et 0004

## Objectif

Empêcher un opérateur local de modifier le périmètre d'un utilisateur non
gérable ou d'ajouter une association qui élargirait illicitement son
propre périmètre d'administration.

## Travail attendu

- Vérifier la gérabilité de l'utilisateur cible.
- Vérifier chaque département ajouté ou retiré côté serveur.
- Protéger les mutations unitaires et en masse.
- Préserver la sémantique explicite de l'ADR 0050.

## Vérifications

- Tests d'intégration des associations.
- Tests API des ajouts, retraits, refus et requêtes hors périmètre.

