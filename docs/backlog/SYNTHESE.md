# Synthèse du backlog

État vérifié le **12 octobre 2026** à partir des fiches de tickets, du
manifeste `validation-applicationform/manifest.json`, du code, des migrations
et des tests présents dans le dépôt. Les statuts ci-dessous décrivent le
reste à faire, pas seulement le statut historique écrit dans une fiche.

## Vue d’ensemble

| Périmètre | Total | Entièrement traité | Partiellement traité | Reste à faire |
|---|---:|---:|---:|---:|
| Backlog technique racine (`0001`–`0016`) | 16 | 13 | 1 | 2 |
| Workflow `validation-applicationform` (`0009`–`0021`) | 13 | 12 | 0 | 1 |
| **Total** | **29** | **25** | **1** | **3** |

Le ticket 0002 est compté comme partiellement traité ; il reste donc six
tickets à réaliser complètement, auxquels s’ajoutent les compléments du 0002.

## Tickets restant à effectuer

| Ticket | Priorité | État après vérification | Reste à faire | Vérification de l’existant |
|---|---|---|---|---|
| [0001 — Gestionnaire de tickets](0001-mettre-en-place-un-gestionnaire-de-tickets.md) | Moyenne | À faire | Choisir et mettre en service l’outil, documenter le processus, migrer les fiches actives et décider du sort de `docs/backlog/`. | Aucun gestionnaire ni migration externe n’est documenté ; le dépôt reste le backlog transitoire. |
| [0002 — Couverture de tests](0002-etendre-la-couverture-de-tests.md) | Haute | **Partiel** | Mesurer et documenter la couverture cible, décider de l’automatisation des seuils (80 % global, 90 % critique, objectif branches), puis traiter l’écart restant. | Tests HTTP, workflow/vues SQL, PCOV et rapports Clover/HTML existent ; la fiche indique elle-même 61,5 % de lignes et des seuils différés. |
| [0007 — Accès menus et permissions](0007-separer-acces-menus-et-permissions-administration.md) | Haute | À faire | Introduire `role_permissions`/`manage_menus`, centraliser la résolution des accès, migrer les données et ajouter les tests HTTP, Policy et intégration. | `src/Policy/MenuPolicy.php` autorise encore via `isSuperUser()` ; aucune table `role_permissions` ni permission `manage_menus` n’a été trouvée. |
| [Workflow 0021 — Concurrence des votes](validation-applicationform/0021-concurrence-votes-meme-role.md) | Urgente | **Partiel non validant** | Garantir atomiquement un seul vote concurrent, définir le retour métier du second votant, rafraîchir l’IHM, afficher `validated` plutôt que `due_at` et couvrir acceptation/refus/API/IHM. | La migration possède `validations_step_un` et le service utilise une transaction ; il n’y a toutefois ni verrouillage explicite ni test de concurrence, et l’API expose encore `due_at` sans l’horodatage métier attendu. |

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
| Workflow 0009 à 0020 | Terminé | Les treize entrées correspondantes du manifeste sont `Terminé` ; les fiches, migrations, services et tests associés sont présents. |

## Conclusion opérationnelle

L’ordre recommandé est :

1. **0021** en priorité urgente, car il porte sur l’intégrité concurrente des
   votes déjà exposés en production applicative ;
2. **0014 → 0015 → 0016**, dans cet ordre, car les deux derniers dépendent du
   contrat de perte de session ;
3. **0007**, après validation de l’ADR et du modèle de permissions ;
4. **0002**, pour décider des seuils et résorber l’écart de couverture ;
5. **0001**, qui relève de la gouvernance et de la migration du backlog.

Cette synthèse est un audit statique du dépôt. Elle confirme la présence des
implémentations et tests versionnés ; elle ne remplace pas l’exécution des
commandes de validation, en particulier pour les scénarios MySQL et les tests
de concurrence.
