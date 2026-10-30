# Backlog technique transitoire

Ce répertoire conserve les travaux planifiés tant que le projet ne dispose pas d'un gestionnaire de tickets.

## Convention

Chaque fiche porte un numéro stable et un intitulé explicite. Elle précise le contexte, la priorité, les critères d'acceptation et les références utiles (ADR, code ou documentation).

Les statuts utilisés sont : `À planifier`, `Prêt`, `En cours`, `Bloqué` et `Terminé`.

La [synthèse exhaustive du backlog](SYNTHESE.md) recense les tickets racine et
le sous-workflow `validation-applicationform`, avec vérification de leur état
dans le code et les tests.

## Tickets actifs

- [0024 — Généraliser les associations des départements](0024-generaliser-les-associations-des-departements.md) — priorité haute ; chantier prioritaire pour les responsables multiples et le catalogue CGR partagé.
- [0001 — Mettre en place un gestionnaire de tickets](0001-mettre-en-place-un-gestionnaire-de-tickets.md) — priorité moyenne.
- [0002 — Étendre la couverture de tests applicatifs](0002-etendre-la-couverture-de-tests.md) — priorité haute.
- [0007 — Séparer les accès aux menus des permissions d'administration](0007-separer-acces-menus-et-permissions-administration.md) — priorité haute ; permissions génériques, Policies CakePHP et périmètres V1 directs, avec la gestion des descendants et des droits individuels différée.
- [0017 — Implémenter les circuits courts locaux de validation](0017-implementer-les-circuits-courts-locaux.md) — priorité moyenne ; à planifier selon l'ADR 0070.
- [0019 — Reprendre les quatre zones dans la vue d'une DAE](0019-reprendre-les-quatre-zones-dans-la-vue-dae.md) — priorité moyenne ; aligner l'onglet « Détails de la demande » sur l'édition en lecture seule.
- [0020 — Fiabiliser la duplication des DAE](0020-fiabiliser-la-duplication-des-dae.md) — priorité haute ; effacer les champs administratifs et expliciter les blocages.
- [0021 — Implémenter les habilitations génériques](0021-implementer-les-habilitations-generiques.md) — priorité haute ; remplacer les droits codés en dur et sécuriser l'accès aux départements.
- [0022 — Mettre à jour le référentiel des départements](0022-mettre-a-jour-le-referentiel-des-departements.md) — priorité moyenne ; actualiser principalement les fils de SEM/Rédac avec une procédure contrôlée.
- [0023 — Migrer la base de données vers une nouvelle instance](0023-migrer-la-base-de-donnees.md) — priorité moyenne ; documenter une exportation/restauration reproductible et vérifiable.

## Tickets terminés

- [0013 — Mutualiser la présentation des erreurs de validation](0013-mutualiser-la-presentation-des-erreurs-validation.md) — priorité moyenne.
- [0014 — Détecter et tracer la perte de session](0014-detecter-et-tracer-la-perte-de-session.md) — priorité haute.
- [0015 — Rediriger vers la connexion après expiration de session](0015-rediriger-vers-la-connexion-apres-expiration-de-session.md) — priorité haute.
- [0016 — Couvrir le parcours de reconnexion](0016-couvrir-le-parcours-de-reconnexion.md) — priorité haute.
- [0018 — Sécuriser la saisie des jours ouvrés d'une DAE](0018-securiser-la-saisie-des-jours-ouvres-dae.md) — priorité haute.

## Migration vers un gestionnaire de tickets

Le ticket `0001` planifie le choix et la mise en place du futur outil. Lors de cette adoption, chaque fiche active sera créée dans l'outil retenu en conservant ses références. Ce répertoire pourra alors être archivé ou supprimé par un commit dédié.
