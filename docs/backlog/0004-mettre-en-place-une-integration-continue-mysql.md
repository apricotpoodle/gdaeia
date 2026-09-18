# 0004 — Formaliser la validation locale MySQL et couverture

**Statut :** Terminé
**Priorité :** Moyenne

## Contexte

La suite locale utilise MySQL `daetf2_test`, car l'application dépend d'un index `FULLTEXT`, de `MATCH ... AGAINST` en mode booléen et de vues SQL de workflow. La couverture initiale est de 61,5 % des lignes (1 630 sur 2 649), mesurée avec PCOV 1.0.12.

Pour une équipe de deux développeurs, maintenir une CI distante n'est pas retenu à ce stade. La validation doit néanmoins rester reproductible et fidèle à MySQL.

## Objectif

Formaliser la procédure locale obligatoire avant partage, fondée sur Docker, MySQL et les cibles Makefile existantes.

## Procédure proposée

Avant chaque partage de branche ou demande de fusion, exécuter depuis la racine :

```sh
make test.all
make test.style
make cs.check
make stan
```

Pour une évolution transversale ou une livraison importante, exécuter aussi
`make test.coverage`. Les rapports Clover et HTML sont temporaires sous `/tmp`;
aucun seuil de couverture ne bloque actuellement l'intégration.

Les tests de base de données s'exécutent exclusivement dans Docker sur MySQL
`daetf2_test`. SQLite reste interdit pour ce périmètre.

## Critères d'acceptation

- La procédure et ses prérequis sont documentés dans le README racine.
- Les quatre contrôles obligatoires sont exécutables localement dans Docker.
- La couverture PCOV demeure disponible sans modifier les sources ni publier
  d'artefact distant.
- Le workflow GitHub incompatible est absent.
- L'ADR qui amende l'ADR 0053 est accepté.

## Références

- [Ticket 0002 — Étendre la couverture de tests applicatifs](0002-etendre-la-couverture-de-tests.md)
- [ADR 0053 — Stratégie de tests unitaires et intégration](../adr/0053-strategie-tests-unitaires-et-integration.md)
- [ADR 0059 — Validation locale avant partage](../adr/0059-validation-locale-avant-partage.md)
