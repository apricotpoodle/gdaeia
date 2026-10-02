# Backlog technique transitoire

Ce répertoire conserve les travaux planifiés tant que le projet ne dispose pas d'un gestionnaire de tickets.

## Convention

Chaque fiche porte un numéro stable et un intitulé explicite. Elle précise le contexte, la priorité, les critères d'acceptation et les références utiles (ADR, code ou documentation).

Les statuts utilisés sont : `À planifier`, `Prêt`, `En cours`, `Bloqué` et `Terminé`.

## Tickets actifs

- [0001 — Mettre en place un gestionnaire de tickets](0001-mettre-en-place-un-gestionnaire-de-tickets.md) — priorité moyenne.
- [0002 — Étendre la couverture de tests applicatifs](0002-etendre-la-couverture-de-tests.md) — priorité haute.
- [0007 — Séparer les accès aux menus des permissions d'administration](0007-separer-acces-menus-et-permissions-administration.md) — priorité haute.
- [0014 — Détecter et tracer la perte de session](0014-detecter-et-tracer-la-perte-de-session.md) — priorité haute.
- [0015 — Rediriger vers la connexion après expiration de session](0015-rediriger-vers-la-connexion-apres-expiration-de-session.md) — priorité haute.
- [0016 — Couvrir le parcours de reconnexion](0016-couvrir-le-parcours-de-reconnexion.md) — priorité haute.

## Tickets terminés

- [0013 — Mutualiser la présentation des erreurs de validation](0013-mutualiser-la-presentation-des-erreurs-validation.md) — priorité moyenne.

## Migration vers un gestionnaire de tickets

Le ticket `0001` planifie le choix et la mise en place du futur outil. Lors de cette adoption, chaque fiche active sera créée dans l'outil retenu en conservant ses références. Ce répertoire pourra alors être archivé ou supprimé par un commit dédié.
