# Synthèse du backlog

État vérifié le **5 novembre 2026** à partir des fiches de tickets, des
manifestes `validation-applicationform/manifest.json` et
`visibilite-manageabilite/manifest.json`, du code, des migrations et des tests
présents dans le dépôt. Les statuts ci-dessous décrivent le reste à faire, pas
seulement le statut historique écrit dans une fiche.

Les tickets 0017 à 0023 ont été ajoutés les **23 et 29 octobre 2026** sans
nouvel audit global. Le ticket 0025 a été ajouté le **4 novembre 2026** ; son
sous-workflow est désormais terminé et l'ADR 0073 est accepté.
Le ticket 0026 formalise le plan transverse en huit étapes et est en cours
depuis le démarrage de son étape 1.

Mise à jour ciblée du **6 novembre 2026** : 0017 est implémenté ; sa configuration
métier reste à saisir par l’opérateur. Les autres constats de cet audit
historique n’ont pas été réévalués dans cette livraison.

## Vue d’ensemble

| Périmètre | Total | Entièrement traité | Partiellement traité | Reste à faire |
|---|---:|---:|---:|---:|
| Backlog technique racine (`0001`–`0026`) | 26 | 17 | 1 | 8 |
| Workflow `validation-applicationform` (`0009`–`0021`) | 13 | 13 | 0 | 0 |
| Workflow `visibilite-manageabilite` (`0001`–`0009`) | 9 | 9 | 0 | 0 |
| **Total** | **48** | **39** | **1** | **8** |

Le ticket 0002 est compté comme partiellement traité ; les tickets restants
concernent les sujets 0001, 0007, 0019, 0021 à 0023, 0025 et 0026, auxquels s'ajoutent
les compléments du 0002.

## Tickets restant à effectuer

| Ticket | Priorité | État après vérification | Reste à faire | Vérification de l’existant |
|---|---|---|---|---|
| [0001 — Gestionnaire de tickets](0001-mettre-en-place-un-gestionnaire-de-tickets.md) | Moyenne | À faire | Choisir et mettre en service l’outil, documenter le processus, migrer les fiches actives et décider du sort de `docs/backlog/`. | Aucun gestionnaire ni migration externe n’est documenté ; le dépôt reste le backlog transitoire. |
| [0002 — Couverture de tests](0002-etendre-la-couverture-de-tests.md) | Haute | **Partiel** | Mesurer et documenter la couverture cible, décider de l’automatisation des seuils (80 % global, 90 % critique, objectif branches), puis traiter l’écart restant. | Tests HTTP, workflow/vues SQL, PCOV et rapports Clover/HTML existent ; la fiche indique elle-même 61,5 % de lignes et des seuils différés. |
| [0007 — Accès menus et permissions](0007-separer-acces-menus-et-permissions-administration.md) | Haute | À faire | Introduire `permissions`/`role_permissions` et `manage_menus`, centraliser la résolution des accès, migrer les données et ajouter les tests HTTP, Policy et intégration. La V1 couvre `self`, les départements directement affectés (`assigned`) et `all` ; les descendants et les droits individuels sont différés. | `src/Policy/MenuPolicy.php` autorise encore via `isSuperUser()` ; aucune table `permissions`/`role_permissions` ni permission `manage_menus` n’a été trouvée. Les Policies et custom finders existants constituent le socle de migration ; le ticket écarte provisoirement ARO/ACO. |
| [0019 — Quatre zones de la vue DAE](0019-reprendre-les-quatre-zones-dans-la-vue-dae.md) | Moyenne | À planifier | Aligner l'onglet « Détails de la demande » sur les quatre zones de l'édition, en lecture seule et avec conservation des droits. | Nouveau ticket créé le 29 octobre ; aucune implémentation engagée. |
| [0020 — Duplication des DAE](0020-fiabiliser-la-duplication-des-dae.md) | Haute | **Terminé** | — | Duplication typée, champs administratifs réinitialisés, brouillons sans budget/inscription budgétaire et erreurs API par catégorie vérifiés. |
| [0021 — Habilitations génériques](0021-implementer-les-habilitations-generiques.md) | Haute | À planifier | Implémenter le premier vertical du socle 0007 : permissions génériques et sécurisation Web/API de l'administration des départements. | L'ADR 0069 est accepté ; le ticket 0007 définit le socle générique et 0021 en constitue l'implémentation spécialisée. |
| [0022 — Référentiel des départements](0022-mettre-a-jour-le-referentiel-des-departements.md) | Moyenne | À planifier | Mettre à jour les départements, principalement les fils de SEM/Rédac, avec import contrôlé et vérification d'intégrité. | Nouveau ticket créé le 29 octobre ; aucune transformation de données engagée. |
| [0023 — Migration de base](0023-migrer-la-base-de-donnees.md) | Moyenne | À planifier | Préparer une migration MySQL simple par export/restauration, migrations et contrôles de cohérence. | Nouveau ticket créé le 29 octobre ; aucune bascule de base engagée. |
| [0024 — Associations multiples des départements](0024-generaliser-les-associations-des-departements.md) | **Haute** | Terminé | Remplacer les responsables et rattachements CGR directs par des tables de liaison, migrer les données et adapter l’ORM, l’API et les écrans. | Migration, ORM, API, IHM et validations finalisées. |
| [0026 — Plan d'architecture et de backlog en huit étapes](0026-executer-le-plan-architecture-et-backlog.md) | **Haute** | **En cours** | Coordonner l'assainissement documentaire, l'acceptation des ADR, les habilitations, la visibilité/gérabilité, les évolutions métier, la qualité, la migration et la gouvernance. | Étape 1 réalisée ; l'étape 2 reste conditionnée à la revue humaine des ADR 0071 et 0072. |

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
| [0017 — Circuits courts locaux](0017-implementer-les-circuits-courts-locaux.md) | Implémenté | Règles locales, habilitations explicites, administration et diagnostics, lancement et conservation des cycles couverts ; configuration métier réelle à saisir par l’opérateur. |
| 0018 — Jours ouvrés des DAE | Terminé | Champ regroupé avec la date de fin, calcul automatique des jours ouvrés, plafond serveur sur la durée calendaire et tests unitaires, ORM, API et Web présents. |
| Workflow 0021 — Concurrence des votes | Terminé | Verrouillage transactionnel de l’étape, contrainte d’unicité, message métier homogène, exposition de l’horodatage du vote et couverture PHPUnit/API/IHM présents. |
| Workflow 0009 à 0020 | Terminé | Les treize entrées correspondantes du manifeste sont `Terminé` ; les fiches, migrations, services et tests associés sont présents. |
| [0025 — Visibilité et gérabilité des utilisateurs](0025-separer-visibilite-et-manageabilite-utilisateurs.md) | Terminé | Le sous-workflow `visibilite-manageabilite/` est terminé ; les neuf sous-tickets, les mutations Web/API, les grilles et la recette transversale sont vérifiés. |

## Conclusion opérationnelle

L’ordre recommandé est :

1. **0026**, pour piloter les huit étapes et corriger d'abord les index du
   backlog, notamment la divergence du ticket 0024 ;
2. **0007**, après validation de l'ADR et du modèle de permissions ;
3. **0002**, pour décider des seuils et résorber l'écart de couverture ;
4. **0001**, qui relève de la gouvernance et de la migration du backlog.
5. **0017** est implémenté ; effectuer la configuration métier manuelle des
   seuls services retenus selon le guide opérateur.
6. **0019**, pour aligner la consultation d'une DAE sur l'organisation de son édition.
7. **0021**, priorité haute, pour mettre en œuvre les habilitations génériques
   et résoudre l'accès administrateur aux départements.
8. **0022**, pour mettre à jour le référentiel des départements SEM/Rédac.
9. **0023**, pour préparer la migration contrôlée de la base MySQL.
10. Pour **0017**, la partie implémentation de l’étape 6 de **0026** est
    réalisée ; la configuration réelle relève de l’opérateur.

Le ticket **0024** est terminé et ne fait plus partie des prochaines étapes.

Cette synthèse est un audit statique du dépôt. Elle confirme la présence des
implémentations et tests versionnés ; elle ne remplace pas l’exécution des
commandes de validation, en particulier pour les scénarios MySQL et les tests
de concurrence.
