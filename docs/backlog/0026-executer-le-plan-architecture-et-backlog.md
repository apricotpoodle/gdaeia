# 0026 — Exécuter le plan d'architecture et de backlog en huit étapes

**Statut :** En cours  
**Priorité :** Haute — pilotage transverse

## Nature du ticket

Ce ticket parent transforme le point d'avancement des ADR et du backlog en un
parcours exécutable sur plusieurs sessions. Chaque étape doit produire un état
intermédiaire vérifiable et laisser le dépôt dans un état compréhensible par
la session suivante.

Le ticket ne remplace pas les tickets métier référencés. Il coordonne leur
ordre, leurs dépendances, leurs validations et la mise à jour de leurs
statuts.

## Règles de reprise entre sessions

Avant chaque session :

1. lire `AGENTS.md`, ce ticket et les tickets de l'étape en cours ;
2. exécuter `rtk git status --short --branch` dans `app/` ;
3. vérifier les ADR dépendants et les modifications déjà présentes ;
4. ne traiter que l'étape active et ses dépendances terminées ;
5. consigner les vérifications effectuées et les points bloquants dans le
   ticket ou le ticket spécialisé concerné.

À la fin de chaque session, l'agent doit laisser :

- les cases réalisées cochées ;
- les tests exécutés et leur résultat ;
- les décisions restant à prendre ;
- une indication explicite de la prochaine étape réalisable.

Une étape ne peut passer à `Terminé` que lorsque ses critères d'acceptation
sont vérifiés. Les validations humaines des ADR et les décisions
d'architecture ne doivent pas être déduites d'une simple implémentation.

## Étapes

### Étape 1 — Assainir les index et le suivi du backlog

**Dépendances :** aucune  
**Sessions conseillées :** 1

- [x] Retirer le ticket 0024 de la section « Tickets actifs » de
  `docs/backlog/README.md` et le placer dans les tickets terminés.
- [x] Vérifier la cohérence entre les statuts des fiches, `README.md`,
  `SYNTHESE.md` et les manifestes des sous-workflows.
- [x] Ajouter le présent ticket aux index et corriger les compteurs concernés.
- [x] Documenter les éventuelles divergences restantes au lieu de les
  corriger silencieusement.

**Critère de sortie :** un nouvel agent peut identifier la source de vérité,
les tickets actifs et la prochaine étape sans interprétation contradictoire.

### Suivi de la session du 5 novembre 2026

- La fiche 0024 était déjà `Terminé`, mais restait présente dans les tickets
  actifs de `README.md` ; elle a été déplacée dans les tickets terminés.
- La synthèse ne reflétait pas le démarrage du ticket 0026 et recommandait à
  tort de traiter à nouveau le ticket 0024 ; son état et l'ordre opérationnel
  ont été corrigés.
- Les deux sous-workflows disposent de manifestes cohérents avec leurs fiches.
  Aucun ticket de ces manifestes n'est actuellement `Terminé` dans
  `visibilite-manageabilite`, tandis que tous ceux de
  `validation-applicationform` le sont.
- La branche de travail est `docs/backlog-0026-etape-1`.

La prochaine étape réalisable est l'étape 2, après revue humaine des ADR 0071
et 0072 ; leur statut reste volontairement `Proposé`.

### Étape 2 — Finaliser les ADR déjà concrétisés

**Dépendances :** étape 1  
**ADR concernés :** 0071 et 0072  
**Tickets associés :** 0024

- [x] Vérifier l’implémentation et les tests de l’ADR 0071 sur le fuseau
  Europe/Paris et le stockage UTC.
- [x] Vérifier la migration, l’ORM, les API, les écrans et les tests issus de
  l'ADR 0072.
- [x] Contrôler les migrations et les vues SQL selon les ADR 0005 et 0006.
- [x] Soumettre les deux ADR à revue humaine et passer leur statut à
  `Accepté` uniquement après validation.

### Suivi de la session du 5 novembre 2026

- L’ADR 0071 a été vérifié : `APP_DEFAULT_TIMEZONE` et le repli
  `Europe/Paris` sont appliqués par PHP et CakePHP ; les connexions MySQL
  restent en UTC ; le fuseau est affiché dans la navigation ; les tests de
  configuration et d’interface sont présents.
- L’ADR 0072 a été vérifié : la migration crée les tables de liaison
  `department_managers` et `department_cgr_codes` avec clés primaires
  composites et clés étrangères en cascade ; elle reprend les associations
  historiques, déduplique le catalogue CGR et supprime les colonnes obsolètes.
  Les associations ORM, le service transactionnel, les API, les écrans et
  les tests correspondants sont présents.
- La migration ne crée ni ne modifie de vue SQL ; le contrôle a été effectué
  conformément aux ADR 0005 et 0006.
- Les deux ADR ont reçu la validation humaine et leur statut est désormais
  `Accepté`.
- Vérifications exécutées : `make test.unit` (92 tests), `make cs.check`,
  `make stan` (aucune erreur), `make test.integration` (114 tests ORM) et
  `make test.api` (160 tests HTTP/API). Les tests MySQL ont confirmé la base
  dédiée `daetf2_test`.

La prochaine étape réalisable est l’étape 3 : arbitrer le modèle générique
d’habilitations de l’ADR 0069.

**Critère de sortie :** les décisions 0071 et 0072 sont acceptées ou disposent
d'une liste documentée de corrections bloquantes.

### Étape 3 — Arbitrer le modèle générique d'habilitations

**Dépendances :** étape 2  
**ADR concerné :** 0069  
**Tickets associés :** 0007 et 0021

- [x] Relire les permissions, les périmètres `self`, `assigned`, `all` et
  `unscoped`, ainsi que le rôle des Policies.
- [x] Décider si les tickets 0007 et 0021 restent séparés ou si 0021 devient
  l'implémentation spécialisée du socle défini par 0007.
- [x] Vérifier le comportement attendu du super-administrateur et de
  l'usurpation d'identité.
- [x] Accepter l'ADR 0069 ou le modifier par un nouvel ADR d'amendement.

### Suivi de la session du 5 novembre 2026 — étape 3

- Le ticket 0007 constitue le socle générique ; le ticket 0021 est sa première
  implémentation spécialisée pour l'administration des départements.
- Le modèle canonique est `permissions`, `permission_scopes` et
  `role_permissions`, avec refus par défaut et attribution explicite des
  périmètres.
- Le super-administrateur contourne explicitement les permissions et
  périmètres organisationnels, sans contourner les invariants métier ou le
  workflow.
- L'usurpation évalue uniquement l'identité effective usurpée ; les droits de
  l'administrateur d'origine ne sont pas hérités.
- L'ADR 0069 est accepté. Aucune migration de schéma ni implémentation n'est
  incluse dans cette étape.
- La prochaine étape réalisable est l'étape 4 : implémenter les permissions et
  habilitations, en commençant par le vertical des départements.

**Critère de sortie :** le modèle de permissions et les frontières de la V1
sont approuvés avant toute migration de schéma.

### Étape 4 — Implémenter les permissions et habilitations

**Dépendances :** étape 3  
**Tickets associés :** 0007 et 0021

- [x] Créer les tables et associations prévues par l'ADR accepté, avec
  contraintes, refus par défaut et migration explicite des droits existants.
- [x] Implémenter la résolution des permissions et des périmètres côté
  Models/Services.
- [x] Aligner menus, URLs Web, endpoints API et `DepartmentPolicy`.
- [x] Ajouter les tests unitaires, ORM, Web et API prévus par les tickets.
- [x] Exécuter les contrôles adaptés : `make test.unit`, `make cs.check`,
  `make stan`, `make test.integration`, `make test.api` et, si nécessaire,
  `make test.workflow`.

### Suivi de la session d'implémentation

- Les permissions `departments.read`, `departments.manage` et `menus.manage`
  sont cataloguées ; les périmètres sont déclarés séparément des attributions.
- La reprise automatique conserve l'accès historique de l'administrateur sur
  ses départements explicitement affectés, sans déduire un droit du seul rôle.
- Les Policies, le finder des départements, les routes Web/API et l'écran du
  catalogue appliquent désormais les capacités centralisées.
- Vérifications ciblées réussies : `make test.unit` (97 tests),
  `make test.integration` (114 tests), `make test.api` (164 tests),
  `make test.style`, `make test.workflow`, `make cs.check` et `make stan`.
- `make test.all` a été exécuté deux fois : 451 tests, avec un seul échec
  préexistant dans `NormalizeDepartmentAssociationsTest`, qui ne retrouve pas
  l'index `idx_cgr_codes_unique_definition` de la migration 0024. Les suites
  unitaires, ORM, HTTP, style et workflow ciblées restent vertes.

**Critère de sortie :** les accès autorisés, les refus hors périmètre et les
accès directs sont testés et cohérents.

### Étape 5 — Séparer visibilité et gérabilité

**Dépendances :** étapes 2, 3 et 4  
**ADR concerné :** 0073  
**Ticket parent :** 0025  
**Sous-workflow :** `docs/backlog/visibilite-manageabilite/`

- [x] Valider le contrat des racines explicites et des périmètres
  multi-branches.
- [ ] Passer l'ADR 0073 à `Accepté` après validation du contrat.
- [ ] Exécuter les sous-tickets 0001 à 0009 dans l'ordre du manifeste.
- [ ] Sécuriser les mutations Web/API, les affectations, les rôles et les
  associations de responsables côté serveur.
- [ ] Effectuer la recette transversale et documenter les cas non gérables en
  lecture seule.

**Critère de sortie :** visibilité et gérabilité sont des décisions distinctes
dans les finders, Policies, services, contrôleurs, API et interfaces.

### Suivi de la sous-étape 5.1

- [x] Exécuter le sous-ticket 0001 : contrat des racines explicites et des
  périmètres multi-branches validé.
- Le contrat du sous-ticket 0001 fixe une représentation canonique par liste
  dédoublonnée d'identifiants issus de `user_departments`.
- Les comparaisons utilisent les bornes `lft`/`rght` des associations
  explicites et évaluent chaque racine d'un périmètre multi-branches.
- Un périmètre non-superuser vide ne donne aucun résultat ; le superuser
  conserve le court-circuit global.
- Les cas contractuels et les scénarios de tests ORM sont documentés dans le
  sous-ticket 0001.
- L'ADR 0073 reste `Proposé` jusqu'à la validation de l'étape 5.2.

La prochaine étape réalisable est l'étape 5.2 : soumettre l'ADR 0073 à
acceptation après validation humaine du contrat.

### Suivi de la sous-étape 5.2

- [x] Exécuter le sous-ticket 0002 : résolution ORM des périmètres explicites.
- Les associations explicites sont dédoublonnées par
  `UserDepartmentsTable::findDepartmentsOf()`.
- Le finder `DepartmentsTable::findStrictAncestorsOf()` compare les bornes
  `lft`/`rght` strictement, sur plusieurs racines, et exclut les départements
  inactifs.
- Vérification exécutée : `make test.integration` (118 tests ORM réussis).
- Le sous-ticket 0003 est désormais le prochain ticket exécutable du
  manifeste.

### Étape 6 — Réaliser les évolutions métier prioritaires

**Dépendances :** étapes 4 et 5 pour les sujets d'habilitations concernés

- [ ] Implémenter 0020 : duplication fiable des DAE et réinitialisation des
  champs administratifs.
- [ ] Implémenter 0019 : consultation organisée selon les quatre zones de
  l'édition, en lecture seule.
- [ ] Implémenter 0017 selon l'ADR 0070 : règles locales, administration,
  lancement, contrôles d'éligibilité et conservation des cycles.
- [ ] Ajouter les tests au niveau pertinent et mettre à jour la documentation
  métier locale.

**Critère de sortie :** chaque ticket est livré séparément, avec ses critères
d'acceptation et ses vérifications propres satisfaits.

### Étape 7 — Couverture, référentiel et migration opérationnelle

**Dépendances :** étapes 2 à 6 selon le périmètre touché

- [ ] Compléter 0002 : mesurer la couverture, fixer les seuils et traiter les
  écarts prioritaires.
- [ ] Réaliser 0022 avec une procédure contrôlée de mise à jour de l'arbre des
  départements et des références associées.
- [ ] Préparer 0023 : export, restauration, contrôle d'intégrité, répétabilité
  et plan de retour arrière, sans exposer de secrets.
- [ ] Vérifier explicitement `DATABASE_TEST_URL` pour les tests MySQL.

**Critère de sortie :** la qualité, le référentiel et la procédure de migration
sont mesurables, reproductibles et documentés.

### Étape 8 — Mettre en place la gouvernance durable et clôturer le plan

**Dépendances :** étapes 1 à 7

- [ ] Réaliser 0001 : choisir et mettre en service le gestionnaire de tickets.
- [ ] Migrer les tickets actifs en conservant leurs références et décisions.
- [ ] Définir le processus de création, revue, acceptation, clôture et
  archivage des tickets et ADR.
- [ ] Refaire un audit global des statuts, ADR, manifestes, tests et
  documentations.
- [ ] Mettre à jour ce ticket en `Terminé` uniquement lorsque toutes les
  étapes sont elles-mêmes terminées.

**Critère de sortie :** le suivi opérationnel ne dépend plus du backlog
transitoire seul et l'état final du dépôt est auditable.

## Vérifications communes

Toute modification applicative doit exécuter `make test.unit`. Selon le
périmètre, ajouter `make cs.check`, `make stan`, `make test.style`,
`make test.integration`, `make test.api`, `make test.workflow` ou
`make test.all`. Les résultats et les non-exécutions justifiées doivent être
consignés dans le ticket concerné.

## Références

- [Synthèse du backlog](SYNTHESE.md)
- [Index des ADR](../adr/README.md)
- [ADR 0069 — Habilitations par rôles et périmètres](../adr/0069-habilitations-par-roles-et-perimetres.md)
- [ADR 0071 — Fuseau horaire applicatif](../adr/0071-fuseau-horaire-applicatif-europe-paris.md)
- [ADR 0072 — Associations multiples](../adr/0072-associations-multiples-departements-cgr.md)
- [ADR 0073 — Visibilité et gérabilité](../adr/0073-visibilite-et-manageabilite-des-utilisateurs.md)
