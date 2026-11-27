# Backlog technique transitoire

Ce répertoire conserve les travaux planifiés tant que le projet ne dispose pas d'un gestionnaire de tickets.

## Convention

Chaque fiche porte un numéro stable et un intitulé explicite. Elle précise le contexte, la priorité, les critères d'acceptation et les références utiles (ADR, code ou documentation).

Les statuts utilisés sont : `À planifier`, `Prêt`, `En cours`, `Bloqué` et
`Terminé`. Les fiches `Implémenté` décrivent une livraison de code dont la
configuration métier ou la recette reste à effectuer par l'opérateur.

La [synthèse exhaustive du backlog](SYNTHESE.md) recense les tickets racine et
les sous-workflows `validation-applicationform` et
`visibilite-manageabilite`, ainsi que le workflow `gdae-v3-completion`. Les
manifestes des sous-workflows sont leur source de vérité opérationnelle.

## Tickets actifs

- [0026 — Exécuter le plan d'architecture et de backlog en huit étapes](0026-executer-le-plan-architecture-et-backlog.md) — priorité haute ; ticket parent reprenable sur plusieurs sessions.
- [0001 — Mettre en place un gestionnaire de tickets](0001-mettre-en-place-un-gestionnaire-de-tickets.md) — priorité moyenne.
- [0002 — Étendre la couverture de tests applicatifs](0002-etendre-la-couverture-de-tests.md) — priorité haute.
- [0007 — Séparer les accès aux menus des permissions d'administration](0007-separer-acces-menus-et-permissions-administration.md) — priorité haute ; permissions génériques, Policies CakePHP et périmètres V1 directs, avec la gestion des descendants et des droits individuels différée.
- [0017 — Implémenter les circuits courts locaux de validation](0017-implementer-les-circuits-courts-locaux.md) — implémenté selon l’ADR 0070 ; configuration métier manuelle à effectuer.
- [0022 — Mettre à jour le référentiel des départements](0022-mettre-a-jour-le-referentiel-des-departements.md) — priorité moyenne ; actualiser principalement les fils de SEM/Rédac avec une procédure contrôlée.
- [0023 — Migrer la base de données vers une nouvelle instance](0023-migrer-la-base-de-donnees.md) — priorité moyenne ; documenter une exportation/restauration reproductible et vérifiable.
- [0041 — Administrer les permissions par rôle](0041-administrer-permissions-par-role.md) — priorité haute ; gestion séquentielle des attributions du catalogue, sans changement de la matrice initiale.
- [0042 — Corriger le filtre des DAE en cours](0042-filtrer-correctement-les-dae-en-cours.md) — priorité moyenne ; inclure tous les cycles actifs, y compris à 0 %, sans retourner les cycles terminés.

## Tickets terminés

- [0027 — Autoriser la création d'une Applicationform par un demandeur](0027-autoriser-creation-applicationform-demandeur.md) — parcours HTTP de création et attribution automatique du créateur vérifiés le 18 novembre 2026 (`make test.api`, 238 tests et 1 015 assertions).
- [0024 — Généraliser les associations des départements](0024-generaliser-les-associations-des-departements.md) — priorité haute.
- [0013 — Mutualiser la présentation des erreurs de validation](0013-mutualiser-la-presentation-des-erreurs-validation.md) — priorité moyenne.
- [0014 — Détecter et tracer la perte de session](0014-detecter-et-tracer-la-perte-de-session.md) — priorité haute.
- [0015 — Rediriger vers la connexion après expiration de session](0015-rediriger-vers-la-connexion-apres-expiration-de-session.md) — priorité haute.
- [0016 — Couvrir le parcours de reconnexion](0016-couvrir-le-parcours-de-reconnexion.md) — priorité haute.
- [0018 — Sécuriser la saisie des jours ouvrés d'une DAE](0018-securiser-la-saisie-des-jours-ouvres-dae.md) — priorité haute.
- [0025 — Séparer la visibilité et la gérabilité des utilisateurs](0025-separer-visibilite-et-manageabilite-utilisateurs.md) — priorité haute.
- [0020 — Fiabiliser la duplication des DAE](0020-fiabiliser-la-duplication-des-dae.md) — priorité haute.
- [0019 — Reprendre les quatre zones dans la vue d'une DAE](0019-reprendre-les-quatre-zones-dans-la-vue-dae.md) — vue en lecture seule livrée et couverte par les tests.
- [0021 — Implémenter les habilitations génériques](0021-implementer-les-habilitations-generiques.md) — vertical d'administration des départements livré.
- [0029 — Protéger les références socles avec un Behavior commun](0029-proteger-references-socles-behavior-commun.md) — Behavior et parcours Web/API livrés ; l'ADR 0075 reste proposé.
- [0028 — Améliorer l'IHM des autorisations de champs](0028-ameliorer-ihm-autorisations-champs.md) — acceptations ORM, API, formulaires Web et migration vérifiées le 18 novembre 2026 (`make test.all`, 621 tests et 2 282 assertions).

## Séquence de finalisation GDAE V3

Les fiches 0030 à 0039, à la racine de `docs/backlog/`, sont exécutées dans l'ordre du manifeste
[gdae-v3-completion/manifest.json](gdae-v3-completion/manifest.json). Utiliser
`bin/cake backlog next gdae-v3-completion` pour identifier la prochaine fiche
et consulter son statut. Le [ticket 0030](0030-arbitrer-et-actualiser-le-perimetre-gdae-v3.md)
est `Terminé`; les réponses métier encore attendues sont suivies dans les
tickets concernés. Le 0031 est `Terminé`; les arbitrages Q-0018-02 à 04 y sont
consignés et appliqués.
Le ticket 0040 est un bonus retenu, hors du manifeste obligatoire et
démarrable indépendamment du 0039.

## Migration vers un gestionnaire de tickets

Le ticket `0001` planifie le choix et la mise en place du futur outil. Lors de cette adoption, chaque fiche active sera créée dans l'outil retenu en conservant ses références. Ce répertoire pourra alors être archivé ou supprimé par un commit dédié.
