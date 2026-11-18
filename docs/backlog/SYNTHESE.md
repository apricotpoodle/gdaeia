# Synthèse du backlog

Cette synthèse combine l'état des tickets racine relevé le 5 novembre 2026,
un audit ciblé effectué le **17 novembre 2026** sur les tickets 0017 à 0021,
0027 à 0029 et l'étape 6 du ticket 0026, et la clôture vérifiée des tickets
0027 et 0028 le **18 novembre 2026**. Les statuts décrivent le travail restant.

## Vue d'ensemble

| Périmètre | Total | Terminé | Partiel | Reste à faire |
|---|---:|---:|---:|---:|
| Backlog racine historique (`0001`–`0029`) | 29 | 22 | 2 | 5 |
| Workflow obligatoire `gdae-v3-completion` (`0030`–`0039`) | 10 | 0 | 0 | 10 |
| Workflow `validation-applicationform` (`0009`–`0021`) | 13 | 13 | 0 | 0 |
| Workflow `visibilite-manageabilite` (`0001`–`0009`) | 9 | 9 | 0 | 0 |
| **Total obligatoire** | **61** | **44** | **2** | **15** |

Les tickets racine 0002 et 0017 sont partiels. Le code et les tests de
0017 sont livrés, mais sa configuration métier SEM reste à saisir par
l'opérateur. Le ticket 0028 est terminé après réussite de la suite complète et
des vérifications ORM, API, Web, migration, style et analyse statique.
Le ticket 0040 est un bonus facultatif et n'entre pas dans ces totaux.

## Travail restant sur le backlog racine

| Ticket | État après vérification | Travail restant |
|---|---|---|
| [0001 — Gestionnaire de tickets](0001-mettre-en-place-un-gestionnaire-de-tickets.md) | À faire | Choisir et mettre en service l'outil, migrer les fiches actives et décider du sort de `docs/backlog/`. |
| [0002 — Couverture de tests](0002-etendre-la-couverture-de-tests.md) | Partiel | Mesurer la couverture cible, décider de l'automatisation des seuils et traiter l'écart restant. PCOV et les rapports Clover/HTML existent déjà. |
| [0007 — Accès menus et permissions](0007-separer-acces-menus-et-permissions-administration.md) | À faire | Généraliser les permissions aux autres domaines, centraliser les règles d'accès et compléter les tests. Le vertical départements livré par 0021 ne clôt pas ce ticket. |
| [0017 — Circuits courts locaux](0017-implementer-les-circuits-courts-locaux.md) | Implémenté, configuration à faire | Saisir les règles et rattachements SEM retenus dans l'environnement convenu ; aucune règle métier n'est créée automatiquement. L'ADR 0074 reste proposé. |
| [0027 — Création par un demandeur](0027-autoriser-creation-applicationform-demandeur.md) | Terminé | Parcours HTTP d'affichage et de création réussie par un demandeur couvert, avec attribution automatique de `user_id` ; `make test.api` réussit avec 238 tests et 1 015 assertions. |
| [0028 — IHM des autorisations de champs](0028-ameliorer-ihm-autorisations-champs.md) | Terminé | Tests ORM, API/Web et migration historique exécutés ; `make test.all` réussit avec 621 tests et 2 282 assertions sur `daetf2_test`. |
| [0022 — Référentiel des départements](0022-mettre-a-jour-le-referentiel-des-departements.md) | À planifier | Obtenir le référentiel officiel SEM/Rédac et réaliser l'import contrôlé. |
| [0023 — Migration de base](0023-migrer-la-base-de-donnees.md) | À planifier | Préparer et recetter la migration vers l'instance MySQL cible. |
| [0026 — Plan d'architecture et de backlog](0026-executer-le-plan-architecture-et-backlog.md) | En cours | Poursuivre les étapes restantes de gouvernance, couverture, référentiel et migration ; l'étape 6 est réalisée côté implémentation et documentation. |

## Séquence obligatoire GDAE V3

Le manifeste [`gdae-v3-completion/manifest.json`](gdae-v3-completion/manifest.json)
est la source de vérité pour l'ordre. Exécuter
`bin/cake backlog next gdae-v3-completion` avant chaque ticket. Les tickets
0031 à 0039 dépendent chacun du précédent ; le 0030 recueille les arbitrages
et met à jour les questions métier avant tout développement dépendant.

| Ticket | Sujet | État |
|---|---|---|
| [0030](0030-arbitrer-et-actualiser-le-perimetre-gdae-v3.md) | Arbitrer les règles métier et actualiser le périmètre | Prêt |
| [0031](0031-aligner-dates-jours-travailles-remuneration.md) | Dates, jours travaillés et rémunération | À planifier |
| [0032](0032-habiliter-administration-locale-par-service.md) | Administration des références métier par les rôles ADMIN dans leur périmètre | À planifier |
| [0033](0033-reprendre-une-dae-pendant-son-circuit.md) | Reprise d'une DAE en cours de circuit | À planifier |
| [0034](0034-finaliser-archivage-et-acces-archives.md) | Archivage et accès aux archives | À planifier |
| [0035](0035-completer-notifications-gdae.md) | Notifications quotidiennes et hebdomadaires | À planifier |
| [0036](0036-ajouter-compteur-demandes-a-valider.md) | Compteur et filtre des DAE à valider | À planifier |
| [0037](0037-completer-donnees-imprimees-pdf.md) | Données imprimées du PDF | À planifier |
| [0038](0038-respecter-affichage-sans-defilement.md) | Affichage selon les dimensions convenues | À planifier |
| [0039](0039-configurer-et-recetter-gdae-v3.md) | Configuration et recette transversale | À planifier |

Le ticket facultatif [0040](0040-parametrer-champs-reinitialises-duplication.md)
est hors manifeste et ne sera examiné qu'après le 0039.

Les questions Q-0018-02 à Q-0018-04 sur les jours travaillés restent ouvertes
et font partie des entrées du 0030 ; les règles de visibilité, de période sans
jour ouvré et de changement de dates (Q-0018-01, 05 et 06) sont décidées.
Les choix relatifs à €/heure, à la reprise et à l'archivage restent ouverts.
Les destinataires et déclencheurs des notifications, les dimensions d'écran
cibles et le maintien en base de `applicationforms.manager_id` sont décidés au
0030 ; les commandes de notification seront planifiées par Airflow.

## Tickets historiques réconciliés

| Ticket | État constaté | Éléments vérifiés |
|---|---|---|
| [0018 — Jours ouvrés](0018-securiser-la-saisie-des-jours-ouvres-dae.md) | Terminé pour son périmètre technique | Calcul hors week-ends, saisie et bornes serveur présents ; les règles métier V3 restent au 0030/0031. |
| [0019 — Vue en quatre zones](0019-reprendre-les-quatre-zones-dans-la-vue-dae.md) | Terminé | Vue en lecture seule, visibilité de la zone réservée et tests fonctionnels présents. |
| [0020 — Duplication des DAE](0020-fiabiliser-la-duplication-des-dae.md) | Terminé | Liste canonique de champs réinitialisés et tests ORM/API présents. Le bonus de paramétrage 0040 n'est pas inclus dans ce ticket. |
| [0021 — Habilitations génériques](0021-implementer-les-habilitations-generiques.md) | Terminé pour le vertical départements | Permissions, périmètres et interfaces de gestion avec tests présents. La généralisation du ticket 0007 reste distincte. |
| [0027 — Création par un demandeur](0027-autoriser-creation-applicationform-demandeur.md) | Terminé | Policy et filtrage serveur livrés ; affichage du formulaire, enregistrement HTTP réussi et attribution automatique de `user_id` vérifiés ; 238 tests et 1 015 assertions réussis dans `make test.api`. |
| [0028 — IHM des autorisations de champs](0028-ameliorer-ihm-autorisations-champs.md) | Terminé | Référentiel, API, formulaires et migration historique vérifiés ; 621 tests et 2 282 assertions réussis dans la suite complète. |
| [0029 — Protection des références socles](0029-proteger-references-socles-behavior-commun.md) | Terminé | Behavior, classe `ReferenceTable`, attachements, tests ORM/API et documentation présents. L'ADR 0075 reste au statut proposé pour revue. |

Les autres tickets terminés du backlog racine gardent leurs statuts et
preuves consignés dans leurs fiches. Les workflows de validation et de
visibilité/gérabilité sont terminés selon leurs manifestes respectifs.

## Vérifications et limites

Les tickets applicatifs précisent les suites à exécuter selon leur périmètre.
Les tests MySQL utilisent exclusivement `daetf2_test` via
`DATABASE_TEST_URL`. La recette transversale du 0039 prévoit `make test.all`
si son environnement de test est disponible. Les statuts historiques sont
des constats documentaires ; ils ne valent pas nouvelle exécution des suites.
