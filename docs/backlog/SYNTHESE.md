# Synthèse du backlog

État vérifié le **30 octobre 2026** à partir des fiches de tickets, du
manifeste `validation-applicationform/manifest.json`, du code, des migrations
et des tests présents dans le dépôt. Les statuts ci-dessous décrivent le
reste à faire, pas seulement le statut historique écrit dans une fiche.

Les tickets 0017 à 0023 ont été ajoutés les **23 et 29 octobre 2026** sans
nouvel audit global.

## Vue d’ensemble

| Périmètre | Total | Entièrement traité | Partiellement traité | Reste à faire |
|---|---:|---:|---:|---:|
| Backlog technique racine (`0001`–`0024`) | 24 | 14 | 1 | 9 |
| Workflow `validation-applicationform` (`0009`–`0021`) | 13 | 13 | 0 | 0 |
| **Total** | **36** | **27** | **1** | **8** |

Le ticket 0002 est compté comme partiellement traité ; les tickets restants
concernent les sujets 0001, 0007, 0017 et 0019 à 0024, auxquels s'ajoutent
les compléments du 0002.

## Tickets restant à effectuer

| Ticket | Priorité | État après vérification | Reste à faire | Vérification de l’existant |
|---|---|---|---|---|
| [0001 — Gestionnaire de tickets](0001-mettre-en-place-un-gestionnaire-de-tickets.md) | Moyenne | À faire | Choisir et mettre en service l’outil, documenter le processus, migrer les fiches actives et décider du sort de `docs/backlog/`. | Aucun gestionnaire ni migration externe n’est documenté ; le dépôt reste le backlog transitoire. |
| [0002 — Couverture de tests](0002-etendre-la-couverture-de-tests.md) | Haute | **Partiel** | Mesurer et documenter la couverture cible, décider de l’automatisation des seuils (80 % global, 90 % critique, objectif branches), puis traiter l’écart restant. | Tests HTTP, workflow/vues SQL, PCOV et rapports Clover/HTML existent ; la fiche indique elle-même 61,5 % de lignes et des seuils différés. |
| [0007 — Accès menus et permissions](0007-separer-acces-menus-et-permissions-administration.md) | Haute | À faire | Introduire `permissions`/`role_permissions` et `manage_menus`, centraliser la résolution des accès, migrer les données et ajouter les tests HTTP, Policy et intégration. La V1 couvre `self`, les départements directement affectés (`assigned`) et `all` ; les descendants et les droits individuels sont différés. | `src/Policy/MenuPolicy.php` autorise encore via `isSuperUser()` ; aucune table `permissions`/`role_permissions` ni permission `manage_menus` n’a été trouvée. Les Policies et custom finders existants constituent le socle de migration ; le ticket écarte provisoirement ARO/ACO. |
| [0017 — Circuits courts locaux](0017-implementer-les-circuits-courts-locaux.md) | Moyenne | À planifier | Implémenter les exceptions par contrat et département, l'administration locale et les contrôles de lancement de l'ADR 0070. | Nouveau ticket créé après l'audit du 13 octobre ; aucune implémentation engagée. |
| [0019 — Quatre zones de la vue DAE](0019-reprendre-les-quatre-zones-dans-la-vue-dae.md) | Moyenne | À planifier | Aligner l'onglet « Détails de la demande » sur les quatre zones de l'édition, en lecture seule et avec conservation des droits. | Nouveau ticket créé le 29 octobre ; aucune implémentation engagée. |
| [0020 — Duplication des DAE](0020-fiabiliser-la-duplication-des-dae.md) | Haute | À planifier | Effacer les champs administratifs lors de la copie et retourner des blocages explicites et actionnables. | Nouveau ticket créé le 29 octobre ; aucune implémentation engagée. |
| [0021 — Habilitations génériques](0021-implementer-les-habilitations-generiques.md) | Haute | À planifier | Mettre en œuvre les permissions génériques et sécuriser l'accès Web/API à l'administration des départements. | Nouveau ticket créé le 29 octobre ; l'ADR 0069 est encore proposé et le ticket 0007 reste à concrétiser. |
| [0022 — Référentiel des départements](0022-mettre-a-jour-le-referentiel-des-departements.md) | Moyenne | À planifier | Mettre à jour les départements, principalement les fils de SEM/Rédac, avec import contrôlé et vérification d'intégrité. | Nouveau ticket créé le 29 octobre ; aucune transformation de données engagée. |
| [0023 — Migration de base](0023-migrer-la-base-de-donnees.md) | Moyenne | À planifier | Préparer une migration MySQL simple par export/restauration, migrations et contrôles de cohérence. | Nouveau ticket créé le 29 octobre ; aucune bascule de base engagée. |
| [0024 — Associations multiples des départements](0024-generaliser-les-associations-des-departements.md) | **Haute** | En cours | Remplacer les responsables et rattachements CGR directs par des tables de liaison, migrer les données et adapter l’ORM, l’API et les écrans. | Migration, ORM, API et IHM engagés ; validations automatisées vertes. |

## Tickets déjà traités — contrôle de non-réouverture

| Ticket | Statut constaté | Éléments vérifiés |
|---|---|---|
| 0003 — Moderniser `Table::get()` des menus | Terminé | Fiche marquée terminée ; inclus dans l’historique des changements du backlog. |
| 0004 — Validation locale MySQL et couverture | Terminé | Procédure MySQL et garde `daetf2_test` documentées dans la fiche. |
| 0005 — Moderniser `TreeBehavior::recover()` | Terminé | Fiche marquée terminée et réalisation documentée. |
| 0006 — Protéger l’URL d’administration des menus | Terminé | Contrôleur, Policy et tests d’accès URL présents ; le sujet distinct 0007 reste ouvert. |
| 0008 — Sécuriser les URL de retour post-authentification | Terminé | Fiche et contrôleur d’authentification documentent le traitement sécurisé du paramètre `redirect`. |
| 0009 — Conformité PHP_CodeSniffer | Terminé | Statut terminé ; ticket dédié aux corrections de style clôturé. |
| 0010 — Rétablir PHPStan dans le conteneur | Terminé | Statut terminé ; outillage PHPStan rétabli dans le dépôt. |
| 0011 — Conformité PHPStan | Clôturé | Fiche explicitement clôturée ; les erreurs restantes éventuelles relèvent d’un nouveau ticket. |
| 0012 — Verrouiller une AF pendant la validation | Terminé | Workflow, Policy et tests de verrouillage présents. |
| 0013 — Présentation des erreurs de validation | Terminé | `ValidationErrorPresenter`, ADR 0052 et tests présents. |
| 0014 — Perte de session | Terminé | Middleware API 401 JSON, journalisation sans secret et tests HTTP présents. |
| 0015 — Reconnexion après expiration | Terminé | Gestionnaire `fetch` mutualisé, message de connexion et tests JavaScript présents. |
| 0016 — Parcours de reconnexion | Terminé | Tests API/Web et tests réseau, timeout et non-rejeu présents. |
| 0018 — Jours ouvrés des DAE | Terminé | Champ regroupé avec la date de fin, calcul automatique des jours ouvrés, plafond serveur sur la durée calendaire et tests unitaires, ORM, API et Web présents. |
| Workflow 0021 — Concurrence des votes | Terminé | Verrouillage transactionnel de l’étape, contrainte d’unicité, message métier homogène, exposition de l’horodatage du vote et couverture PHPUnit/API/IHM présents. |
| Workflow 0009 à 0020 | Terminé | Les treize entrées correspondantes du manifeste sont `Terminé` ; les fiches, migrations, services et tests associés sont présents. |

## Conclusion opérationnelle

L’ordre recommandé est :

1. **0014 → 0015 → 0016**, dans cet ordre, car les deux derniers dépendent du
   contrat de perte de session ;
2. **0007**, après validation de l’ADR et du modèle de permissions ;
3. **0002**, pour décider des seuils et résorber l’écart de couverture ;
4. **0001**, qui relève de la gouvernance et de la migration du backlog.
5. **0017**, lorsque l'évolution de la rédaction SEM sera programmée ; suivre
   l'ADR 0070 sans étendre l'exception aux autres sociétés.
6. **0019**, pour aligner la consultation d'une DAE sur l'organisation de son édition.
7. **0020**, pour fiabiliser la copie des DAE et rendre les blocages résolubles.
8. **0021**, priorité haute, pour mettre en œuvre les habilitations génériques
   et résoudre l'accès administrateur aux départements.
9. **0022**, pour mettre à jour le référentiel des départements SEM/Rédac.
10. **0023**, pour préparer la migration contrôlée de la base MySQL.
11. **0024**, en priorité, pour normaliser les responsables et les codes CGR
    avant toute évolution dépendant du référentiel des départements.

Cette synthèse est un audit statique du dépôt. Elle confirme la présence des
implémentations et tests versionnés ; elle ne remplace pas l’exécution des
commandes de validation, en particulier pour les scénarios MySQL et les tests
de concurrence.
