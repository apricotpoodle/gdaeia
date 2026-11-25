# 0030 — Arbitrer et actualiser le périmètre GDAE V3

**Statut :** Terminé

**Priorité :** Haute — prérequis de décision

**Dépendance :** Aucune. Ce ticket ouvre le workflow
`gdae-v3-completion`.

## Contexte

Le cahier des charges GDAE V3 conserve des règles incomplètes et plusieurs
tickets historiques décrivent des éléments déjà livrés. Les décisions
métiers doivent être consignées avant d'engager les développements qui en
dépendent. Les réponses restent à fournir par le métier ; ce ticket ne les
présume pas.

Le code actuel calcule les jours ouvrés hors week-ends, accepte `working_days`
vide et borne sa valeur à la durée calendaire. La visibilité du champ a été
décidée : dates de début et de fin valides, sauf CDI dont la date de fin est
désactivée. Le caractère obligatoire de la saisie, l'usage de la valeur
calculée avant l'envoi, l'acceptation d'une saisie manuelle de zéro pour une
période avec des jours de semaine et le traitement des jours fériés restent
à arbitrer.
`SalaryCalculationService` calcule les
unités journalière, k/mois et €/mois, mais rejette actuellement €/heure. Ces
constats techniques ne constituent pas des décisions métier.

Les questions Q-0018-01 à Q-0018-06 de
[`questions-ouvertes.md`](../meetings/questions-ouvertes.md) sont une
entrée de ce ticket. À la clôture du 0030, les réponses métier à Q-0018-01,
Q-0018-05 et Q-0018-06 étaient consignées; Q-0018-02 à Q-0018-04 restaient
à arbitrer avant le ticket 0031.

L'existant montre aussi des écarts à vérifier : une Policy et une API
d'archivage sont présentes, avec des rôles qui ne recouvrent pas exactement
les rôles cités dans le cahier des charges ; les commandes de synthèse
quotidienne et hebdomadaire existent. Le modèle `Applicationform` contient
`manager_id`, encore utilisé comme destinataire dans un courriel de l'API ;
`departments.current_manager_id` est l'ancienne colonne de responsable unique,
retirée lors de la migration vers les associations multiples. Le métier a
décidé de conserver la structure et les valeurs de `applicationforms.manager_id`
tout en cessant de l'utiliser dans l'application.

Les ADR 0056 et 0063 décrivent déjà deux mécanismes liés à la reprise : un
administrateur autorisé peut supprimer le cycle, ses votes et ses étapes, puis
relancer la DAE ; il peut aussi outrepasser une étape en attente ou à venir.
La remise à zéro ne conserve pas de piste d'audit applicative pour le cycle
supprimé. Il reste à vérifier avec le métier si ces mécanismes suffisent.

## Cadrage du plan approuvé

- Retirer €/heure des nouvelles saisies et conserver l'historique.
- Réutiliser la remise à zéro et la relance existantes. La remise à zéro
  est réservée aux cycles qui n'ont pas atteint leur dernière séquence
  configurée, selon l'ADR 0076 proposé.
- Archiver manuellement, avec restauration possible, en excluant les
  cycles actifs. Appliquer les droits habituels de lecture et conserver
  les données sans purge automatique.
- Vérifier les dimensions de Q-0030-05 dans Chrome, Edge et Firefox
  récents et consigner leurs versions.
- Réaliser la recette dans Docker local isolé avec des données
  synthétiques. L'opérateur configure les séquences par service, sans ordre
  des rôles codé en dur. Airflow et la production relèvent de l'exploitation.

## Travail attendu

- [x] Vérifier les statuts et les éléments réalisés des tickets racine 0017 à
  0021 et 0027 à 0029, ainsi que l'étape 6 du ticket 0026, contre le code,
  les tests et les documents livrés.
- [x] Recenser le caractère obligatoire du champ de jours travaillés, la
  saisie manuelle de zéro en période avec des jours de semaine, l'usage de
  la valeur automatique avant envoi et le traitement des jours fériés
  (Q-0018-02 à Q-0018-04) et les laisser explicitement à arbitrer sans
  déduire de réponse.
- [x] Consigner séparément le cadrage approuvé sur €/heure et laisser la
  réponse métier Q-0030-01 ouverte.
- [x] Proposer la limite de reprise dans l'ADR 0076 et laisser la
  confirmation métier sur ses effets (Q-0030-02) ouverte.
- [x] Consigner le cadrage d'archivage approuvé et laisser les délais et la
  conservation à préciser par le métier (Q-0030-03).
- [x] Ajouter Q-0030-07 pour maintenir ouverte la liste des champs
  additionnels à imprimer tant qu'elle n'est pas confirmée.
- [x] Confirmer les destinataires et les déclencheurs des notifications :
  envoi en temps réel lors des changements d'état du cycle, rappels quotidiens
  et synthèse hebdomadaire ; les commandes récurrentes seront planifiées par
  Airflow hors du périmètre de l'application.
- [x] Définir l'écran de référence et les dimensions de contrôle de l'exigence
  « sans ascenseur » : 1680 × 1050, zone de page 1440 × 900 et contrôle
  complémentaire 1366 × 768, à 100 % de zoom.
- [x] Consigner les dimensions comme critères de recette. L'ADR 0048 ne
  fixe aucune dimension. Le ticket 0038 vérifiera si la solution proposée
  exige un amendement à son découpage en zones.
- [x] Confirmer le devenir de `applicationforms.manager_id` : garder la
  structure et les valeurs existantes, ignorer le champ dans l'application et
  s'appuyer sur les associations multiples de responsables du service.
- [x] Vérifier l'existant de l'archivage et des notifications avant de
  compléter les règles : une API d'archivage, des synthèses quotidiennes et
  hebdomadaires sont déjà présentes, mais leurs rôles, destinataires,
  horaires et planifications doivent être recettés.
- [x] Réconcilier le cahier des charges, le registre, les fiches et les
  index sur la base des réponses reçues et du cadrage approuvé, et proposer
  l'ADR 0076.
- [x] Maintenir les réponses absentes à `À poser` ou `À clarifier` et
  référencer les points bloquants dans les tickets concernés. Répercuter
  toute réponse ultérieure dans le suivi correspondant.

## Critères d'acceptation

1. Chaque question porte une réponse métier fidèle et un exemple si fournis,
   ou reste explicitement « À poser » ou « À clarifier » sans réponse déduite
   du code.
2. Aucun développement dépendant n'est présenté comme prêt tant que sa règle
   métier reste indécise ; les tickets dépendants référencent le blocage.
3. Les statuts des tickets historiques correspondent aux preuves présentes ;
   leurs travaux déjà livrés ne sont pas replanifiés dans la séquence V3.
4. Les références de tickets et les liens documentaires sont valides.

## Vérifications

- Vérifier les réponses et décisions avec l'opérateur métier concerné.
- Vérifier les liens des tickets et des index ainsi que le JSON du manifeste.
- Exécuter `git diff --check`.

## État documentaire au 25 novembre 2026

L'audit a confirmé la livraison du code de 0017 (configuration SEM à saisir
par l'opérateur), 0019, 0021, 0027, 0028 et 0029 ; 0018 et 0020 restent
terminés dans leurs périmètres documentés. Les acceptations du 18 novembre
2026 sont consignées dans les fiches [0027](0027-autoriser-creation-applicationform-demandeur.md)
(`make test.api`, 238 tests et 1 015 assertions) et
[0028](0028-ameliorer-ihm-autorisations-champs.md) (`make test.all`, 621 tests
et 2 282 assertions). Les deux tickets sont terminés. L'étape 6 de 0026 est
réconciliée. Le ticket 0030 est terminé. À cette clôture, les questions
Q-0018-02 à Q-0018-04, Q-0030-01 à Q-0030-03 et Q-0030-07 restaient ouvertes.
Les réponses Q-0018-02 à Q-0018-04 et le cadrage de Q-0030-01 sont depuis
consignés et appliqués au ticket 0031. Q-0030-02, Q-0030-03 et Q-0030-07
bloquent respectivement les tickets 0033, 0034 et 0037. L'ADR 0075 demeure
`Proposé`. Les réponses à Q-0030-04, Q-0030-05 et
Q-0030-06 sont consignées ; la réalisation des commandes et notifications
reste couverte par le ticket 0035.

## Références

- [Cahier des charges GDAE V3](../meetings/cahier_des_charges_gdae_v3.md)
- [Questions métier ouvertes](../meetings/questions-ouvertes.md)
- [ADR 0048 — Découpage de l'IHM Applicationform](../adr/0048-decoupage-ihm-zones-applicationform.md)
- [ADR 0072 — Associations multiples de départements et CGR](../adr/0072-associations-multiples-departements-cgr.md)
- [ADR 0076 proposé — Limiter la remise à zéro avant la dernière séquence](../adr/0076-limiter-remise-a-zero-avant-derniere-sequence.md)
