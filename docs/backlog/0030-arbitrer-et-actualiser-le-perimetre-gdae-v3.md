# 0030 — Arbitrer et actualiser le périmètre GDAE V3

**Statut :** Prêt

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
désactivée. L'usage de la valeur calculée avant l'envoi et l'acceptation d'une
saisie manuelle de zéro pour une période avec des jours de semaine restent à
arbitrer.
`SalaryCalculationService` calcule les
unités journalière, k/mois et €/mois, mais rejette actuellement €/heure. Ces
constats techniques ne constituent pas des décisions métier.

Les questions Q-0018-01 à Q-0018-06 de
[`questions-ouvertes.md`](../meetings/questions-ouvertes.md) sont une
entrée de ce ticket. Les réponses métier à Q-0018-01, Q-0018-05 et Q-0018-06
sont consignées ; Q-0018-02 à Q-0018-04 restent ouvertes.

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

## Travail attendu

- [x] Vérifier les statuts et les éléments réalisés des tickets racine 0017 à
  0021 et 0027 à 0029, ainsi que l'étape 6 du ticket 0026, contre le code,
  les tests et les documents livrés.
- [ ] Compléter ou clôturer les questions ouvertes sur les jours travaillés :
  saisie manuelle de zéro, usage de la valeur calculée avant envoi et
  traitement des jours fériés. Les règles d'alerte pour zéro et de changement
  des dates sont consignées dans les réponses métier.
- [ ] Arbitrer le maintien de l'unité €/heure. Si elle est conservée, consigner
  la formule du salaire de base et la source du nombre d'heures. Proposer sa
  suppression si le métier confirme qu'elle n'est pas utilisée.
- [ ] Vérifier si la remise à zéro suivie d'une relance, combinée à
  l'outrepassement des étapes en attente ou à venir, répond au besoin de
  récupération. Si elle ne suffit pas, définir les acteurs, étapes, votes et
  traces de l'action de récupération à ajouter.
- [ ] Confirmer si l'archivage est manuel ou automatique et ses délais, la
  durée de conservation, ainsi que les rôles et périmètres autorisés à
  rechercher et consulter les archives.
- [x] Confirmer les destinataires et les déclencheurs des notifications :
  envoi en temps réel lors des changements d'état du cycle, rappels quotidiens
  et synthèse hebdomadaire ; les commandes récurrentes seront planifiées par
  Airflow hors du périmètre de l'application.
- [x] Définir l'écran de référence et les dimensions de contrôle de l'exigence
  « sans ascenseur » : 1680 × 1050, zone de page 1440 × 900 et contrôle
  complémentaire 1366 × 768, à 100 % de zoom.
- [ ] Vérifier si les dimensions retenues amendent l'ADR 0048.
- [x] Confirmer le devenir de `applicationforms.manager_id` : garder la
  structure et les valeurs existantes, ignorer le champ dans l'application et
  s'appuyer sur les associations multiples de responsables du service.
- [x] Vérifier l'existant de l'archivage et des notifications avant de
  compléter les règles : une API d'archivage, des synthèses quotidiennes et
  hebdomadaires sont déjà présentes, mais leurs rôles, destinataires,
  horaires et planifications doivent être recettés.
- [ ] Mettre à jour le cahier des charges, `questions-ouvertes.md`, les
  tickets et les index en fonction des réponses réellement reçues.

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

## État documentaire au 18 novembre 2026

L'audit a confirmé la livraison du code de 0017 (configuration SEM à saisir
par l'opérateur), 0019, 0021, 0027, 0028 et 0029 ; 0018 et 0020 restent
terminés dans leurs périmètres documentés. Les tickets 0027 et 0028 restent
partiels jusqu'à l'ajout et la réussite de leurs tests d'acceptation décrits
dans leurs fiches. L'étape 6 de 0026 est réconciliée. Les questions métier
restent ouvertes et bloquent les tickets dépendants jusqu'à leur arbitrage.
L'ADR 0075 demeure `Proposé`. Les réponses à Q-0030-04, Q-0030-05 et
Q-0030-06 sont consignées ; la réalisation des commandes et notifications
reste couverte par le ticket 0035.

## Références

- [Cahier des charges GDAE V3](../meetings/cahier_des_charges_gdae_v3.md)
- [Questions métier ouvertes](../meetings/questions-ouvertes.md)
- [ADR 0048 — Découpage de l'IHM Applicationform](../adr/0048-decoupage-ihm-zones-applicationform.md)
- [ADR 0072 — Associations multiples de départements et CGR](../adr/0072-associations-multiples-departements-cgr.md)
