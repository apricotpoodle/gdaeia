# 0005 — Sécuriser les affectations `user_departments`

**Statut :** Terminé

**Dépendances :** 0003 et 0004

## Objectif

Empêcher un opérateur local de modifier le périmètre d'un utilisateur non
gérable ou d'ajouter une association qui élargirait illicitement son
propre périmètre d'administration.

## Travail attendu

- [x] Vérifier la gérabilité de l'utilisateur cible.
- [x] Vérifier chaque département ajouté ou retiré côté serveur.
- [x] Protéger les mutations unitaires et en masse.
- [x] Préserver la sémantique explicite de l'ADR 0050.

## Vérifications

- [x] Tests d'intégration des associations.
- [x] Tests API des ajouts, retraits, refus et requêtes hors périmètre.

## Réalisation

`UserDepartmentAuthorizationService` centralise le contrôle serveur des
associations explicites. Il compare l'état persistant et l'état hydraté afin
de contrôler aussi bien les ajouts que les retraits, puis refuse toute
association hors du périmètre visible de l'opérateur local.

Les créations et éditions Web/API ainsi que les mutations API unitaires et de
masse appliquent ce contrôle avant toute écriture. Les mutations refusées ne
modifient aucune association existante.

Vérifications exécutées : `make test.api` (170 tests), `make test.unit`,
`make test.integration`, `make test.style`, `make cs.check` et `make stan`.

