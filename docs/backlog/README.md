# Backlog technique transitoire

Ce répertoire conserve les travaux planifiés tant que le projet ne dispose pas d'un gestionnaire de tickets.

## Convention

Chaque fiche porte un numéro stable et un intitulé explicite. Elle précise le contexte, la priorité, les critères d'acceptation et les références utiles (ADR, code ou documentation).

Les statuts utilisés sont : `À planifier`, `Prêt`, `En cours`, `Bloqué` et `Terminé`.

## Tickets actifs

- [0001 — Mettre en place un gestionnaire de tickets](0001-mettre-en-place-un-gestionnaire-de-tickets.md) — priorité moyenne.
- [0002 — Étendre la couverture de tests applicatifs](0002-etendre-la-couverture-de-tests.md) — priorité haute.
- [0002 — Mutualiser la présentation des erreurs de validation](0002-mutualiser-la-presentation-des-erreurs-validation.md) — priorité moyenne.
- [0005 — Moderniser l'appel TreeBehavior::recover() des menus](0005-moderniser-lappel-treebehavior-recover-des-menus.md) — priorité moyenne.
- [0007 — Séparer les accès aux menus des permissions d'administration](0007-separer-acces-menus-et-permissions-administration.md) — priorité haute.
- [0008 — Sécuriser les URL de retour post-authentification](0008-securiser-les-url-de-retour-post-authentification.md) — priorité moyenne.
- [0009 — Remettre l’application en conformité PHP_CodeSniffer](0009-remettre-en-conformite-phpcs.md) — priorité haute.
- [0010 — Rétablir PHPStan dans le conteneur applicatif](0010-retablir-phpstan-conteneur.md) — priorité haute.
- [0011 — Remettre l’application en conformité PHPStan](0011-remettre-en-conformite-phpstan.md) — priorité haute.

## Migration vers un gestionnaire de tickets

Le ticket `0001` planifie le choix et la mise en place du futur outil. Lors de cette adoption, chaque fiche active sera créée dans l'outil retenu en conservant ses références. Ce répertoire pourra alors être archivé ou supprimé par un commit dédié.
