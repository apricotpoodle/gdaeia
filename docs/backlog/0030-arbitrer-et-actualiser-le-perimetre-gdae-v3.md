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
vide et borne sa valeur à la durée calendaire. La visibilité et l'obligation
selon le contrat restent à arbitrer. `SalaryCalculationService` calcule les
unités journalière, k/mois et €/mois, mais rejette actuellement €/heure. Ces
constats techniques ne constituent pas des décisions métier.

Les questions Q-0018-01 à Q-0018-06 de
[`questions-ouvertes.md`](../meetings/questions-ouvertes.md) sont une
entrée de ce ticket et restent ouvertes jusqu'à réponse métier.

L'existant montre aussi des écarts à vérifier : une Policy et une API
d'archivage sont présentes, avec des rôles qui ne recouvrent pas exactement
les rôles cités dans le cahier des charges ; les commandes de synthèse
quotidienne et hebdomadaire existent, mais les destinataires et planifications
doivent être vérifiés ; `current_manager_id` reste dans le modèle
`Applicationform`, alors que le cahier des charges demande qu'il n'y figure
pas. Ces constats ne valent pas arbitrage.

## Travail attendu

- [x] Vérifier les statuts et les éléments réalisés des tickets racine 0017 à
  0021 et 0027 à 0029, ainsi que l'étape 6 du ticket 0026, contre le code,
  les tests et les documents livrés.
- [ ] Compléter ou clôturer les questions ouvertes sur les jours travaillés :
  visibilité et caractère obligatoire selon le contrat, jours fériés,
  périodes sans jour ouvré, correction manuelle et cohérence avec les dates.
- [ ] Consigner la formule attendue pour l'unité €/heure, ou confirmer qu'elle
  n'est pas calculée.
- [ ] Définir ce que signifie « récupérer » une DAE déjà transmise : acteurs,
  étapes/votes conservés ou annulés et traçabilité.
- [ ] Définir le déclenchement, les rôles autorisés et les critères
  d'archivage, ainsi que les droits de recherche et de consultation des
  archives.
- [ ] Confirmer les destinataires et les heures d'envoi des notifications
  quotidiennes et de la synthèse hebdomadaire.
- [ ] Définir les dimensions d'écran cibles derrière l'exigence « sans
  ascenseur » et vérifier si elle amende l'ADR 0048.
- [ ] Confirmer le devenir de l'ancien champ `current_manager_id` et
  l'articulation avec les associations multiples de responsables de
  départements.
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

## État documentaire au 17 novembre 2026

L'audit a confirmé la livraison du code de 0017 (configuration SEM à saisir
par l'opérateur), 0019, 0021, 0027, 0028 et 0029 ; 0018 et 0020 restent
terminés dans leurs périmètres documentés. Les tickets 0027 et 0028 restent
partiels jusqu'à l'ajout et la réussite de leurs tests d'acceptation décrits
dans leurs fiches. L'étape 6 de 0026 est réconciliée. Les questions métier
restent ouvertes et bloquent les tickets dépendants jusqu'à leur arbitrage.
L'ADR 0075 demeure `Proposé`.

## Références

- [Cahier des charges GDAE V3](../meetings/cahier_des_charges_gdae_v3.md)
- [Questions métier ouvertes](../meetings/questions-ouvertes.md)
- [ADR 0048 — Découpage de l'IHM Applicationform](../adr/0048-decoupage-ihm-zones-applicationform.md)
- [ADR 0072 — Associations multiples de départements et CGR](../adr/0072-associations-multiples-departements-cgr.md)
