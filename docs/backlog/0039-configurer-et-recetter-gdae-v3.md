# 0039 — Configurer et recetter GDAE V3

**Statut :** À planifier

**Priorité :** Haute — recette transversale

**Dépendance :** [0038 — Respecter l'affichage sans défilement](0038-respecter-affichage-sans-defilement.md).

## Objectif

Faire saisir et vérifier les séquences, règles de circuits, rattachements et
planifications dans l'environnement convenu, puis consigner les résultats de
recette.

## Travail attendu

- [ ] Confirmer avec l'opérateur l'environnement, le jeu de données et les
  comptes de recette ; exclure toute configuration de production sans
  validation opérationnelle distincte.
- [ ] Saisir les séquences, rôles, règles de circuit court et rattachements
  explicitement retenus.
- [ ] Vérifier les commandes et planifications de notifications sans
  déclencher d'envois non prévus.
- [ ] Clôturer les tests d'acceptation encore ouverts aux tickets 0027 et
  0028, puis consigner leurs résultats avant de déclarer la recette aboutie.
- [ ] Exécuter les scénarios transversaux de création, édition, validation,
  reprise, archivage, recherche, impression et droits.
- [ ] Consigner la configuration, les résultats, anomalies, limites et
  décisions résiduelles dans le compte rendu de recette local.

## Critères d'acceptation

1. L'environnement et les comptes utilisés sont identifiés et autorisés.
2. Les règles saisies correspondent aux décisions 0030 et aux critères des
   tickets 0031 à 0038.
3. Les scénarios de recette couvrent les rôles, périmètres et circuits
   concernés ; les résultats sont reproductibles.
4. Les anomalies restantes sont reliées à un ticket, avec sévérité et suite.
5. Les tests d'acceptation ouverts aux tickets 0027 et 0028 sont terminés et
   leurs résultats sont consignés.
6. Aucune configuration de production n'est supposée ni appliquée par ce
   ticket.

## Vérifications

- Exécuter les suites ciblées requises par les changements précédents.
- Exécuter `make test.all` avant la recette transversale si l'environnement
  et les données de test sont disponibles.
- Vérifier explicitement que les suites MySQL utilisent `daetf2_test`.
- Effectuer et documenter la recette dans l'environnement convenu.

## Références

- [Guide des circuits de validation](../validation-circuit-rules.md)
- [ADR 0070 — Circuits courts locaux par contrat](../adr/0070-circuits-courts-locaux-par-contrat.md)
- Tickets [0030](0030-arbitrer-et-actualiser-le-perimetre-gdae-v3.md) à
  [0038](0038-respecter-affichage-sans-defilement.md)
