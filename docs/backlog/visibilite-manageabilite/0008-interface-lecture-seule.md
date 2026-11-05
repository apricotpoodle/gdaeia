# 0008 — Adapter l'interface aux utilisateurs non gérables

**Statut :** Terminé

**Dépendances :** 0005, 0006 et 0007

## Objectif

Rendre visible la différence entre consultation et mutation sans faire de
l'interface une frontière de sécurité.

## Travail attendu

- [x] Marquer les lignes non gérables en lecture seule.
- [x] Désactiver les actions de modification, association et dissociation.
- [x] Afficher les messages métier en français.
- [x] Conserver l'affichage des associations existantes nécessaires à la lecture.
- [x] Ne pas ajouter de logique d'autorisation uniquement en JavaScript.

## Vérifications

- [x] Tests JavaScript et tests fonctionnels des grilles.
- [x] Vérification que les actions masquées restent refusées côté serveur.

## Réalisation

Les APIs des grilles exposent désormais `is_manageable` et un message français
de lecture seule. Les lignes visibles mais non gérables sont marquées dans les
grilles utilisateurs et d'associations ; leurs actions d'édition, d'association
et de dissociation sont désactivées côté interface. Les contrôles serveur
restent obligatoires et inchangés comme frontière d'autorisation.

Les associations existantes restent affichées afin de permettre la lecture,
mais les lignes hors périmètre ne déclenchent aucune mutation depuis
l'interface.

## Vérifications exécutées

- [x] `node --test tests/js/*.test.mjs` — 8 fichiers, 8 réussis.
- [x] `php -l src/Controller/Api/UsersController.php` — syntaxe valide.
- [x] `git diff --check` — aucune erreur.
- [x] `make test.unit` — 103 tests réussis.
- [x] `make test.api` — 177 tests, 623 assertions réussis sur `daetf2_test`.
- [x] `make test.style` — style des tests validé.
- [x] `make cs.check` — PHPCS validé.
- [x] `make stan` — PHPStan validé, aucune erreur.

