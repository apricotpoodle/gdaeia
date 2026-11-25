# 0039 — Configurer et recetter GDAE V3

**Statut :** À planifier

**Priorité :** Haute — recette transversale

**Dépendance :** [0038 — Respecter l'affichage sans défilement](0038-respecter-affichage-sans-defilement.md).

## Objectif

Faire saisir et vérifier les séquences, règles de circuits et rattachements
dans Docker local isolé avec des données synthétiques, puis consigner les
résultats de recette.

## Travail attendu

- [ ] Identifier l'environnement Docker local isolé, le jeu de données
  synthétiques et les comptes de recette avec l'opérateur.
- [ ] Faire configurer par l'opérateur les séquences, rôles, règles de
  circuit court et rattachements par service avec les mécanismes existants.
  L'ordre des rôles reste configurable, sans codage en dur.
- [ ] Vérifier les commandes CLI et Make de notifications localement avec
  les destinataires de recette, sans configurer Airflow ni la production.
- [ ] Exécuter les scénarios transversaux de création, édition, validation,
  reprise, archivage, recherche, impression et droits.
- [ ] Consigner la configuration, les résultats, anomalies, limites et
  décisions résiduelles dans le compte rendu de recette local.

## Critères d'acceptation

1. L'environnement Docker local est isolé, les données sont synthétiques
   et les comptes de recette sont identifiés.
2. Les règles saisies correspondent aux décisions 0030 et aux critères des
   tickets 0031 à 0038.
3. Les scénarios de recette couvrent les rôles, périmètres et circuits
   concernés ; les résultats sont reproductibles.
4. Les anomalies restantes sont reliées à un ticket, avec sévérité et suite.
5. La recette s'exécute avec les séquences configurées par service et les
   commandes locales. Airflow et la production relèvent de l'exploitation.

## Vérifications

- Exécuter les suites ciblées requises par les changements précédents.
- Exécuter `make test.all` avant la recette transversale si l'environnement
  et les données de test sont disponibles.
- Vérifier explicitement que les suites MySQL utilisent `daetf2_test`.
- Effectuer et documenter la recette dans Docker local isolé.

## Références

- [Guide des circuits de validation](../validation-circuit-rules.md)
- [ADR 0070 — Circuits courts locaux par contrat](../adr/0070-circuits-courts-locaux-par-contrat.md)
- Tickets [0030](0030-arbitrer-et-actualiser-le-perimetre-gdae-v3.md) à
  [0038](0038-respecter-affichage-sans-defilement.md)
- Acceptations déjà consignées dans les fiches
  [0027](0027-autoriser-creation-applicationform-demandeur.md) et
  [0028](0028-ameliorer-ihm-autorisations-champs.md)
