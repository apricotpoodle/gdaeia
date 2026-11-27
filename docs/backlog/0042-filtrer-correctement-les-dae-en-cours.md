# 0042 — Corriger le filtre des DAE en cours

**Statut :** À planifier

**Priorité :** Moyenne — fiabilité de la liste des demandes

## Contexte

Dans la liste principale des Demandes de recrutement, le filtre de la colonne
« Validation » intitulé « En cours » présente un résultat incohérent. Selon le
signalement, il inclut des DAE « Acceptée » et omet des DAE dont le cycle est
actif à 0 %. Le bouton « À valider » fournit la liste attendue d'après le
signalement.

Le filtre « En cours » doit représenter toutes les DAE dont le cycle de
validation est lancé et non terminé, indépendamment de leur progression ou de
la possibilité pour l'utilisateur courant de voter. Le bouton « À valider »
conserve son comportement propre. Le problème est rapporté sur la liste
principale ; sa présence dans les archives et sa variation selon le compte,
le rôle ou le département n'ont pas été vérifiées.

## Objectif

Rendre le filtre de statut cohérent avec l'état du cycle de validation affiché
pour chaque DAE, tout en conservant le filtrage distant et les périmètres
d'accès existants.

## Travail attendu

- Reproduire le cas avec des DAE en cours à 0 %, en cours avec progression,
  acceptées, refusées, annulées et sans cycle lancé.
- Tracer la valeur affichée et le filtre transmis de la grille à la requête
  serveur afin d'identifier la cause avant de la corriger.
- Corriger le filtrage à sa source et ajouter une couverture de non-régression
  pour les états concernés.
- Vérifier que le bouton « À valider » conserve son comportement et que les
  périmètres d'accès restent appliqués côté serveur.

## Critères d'acceptation

1. Le filtre « En cours » inclut les DAE dont le cycle est actif, y compris à
   0 %.
2. Il exclut les DAE acceptées, refusées, annulées et celles sans cycle lancé.
3. Les autres états du filtre ne retournent que des DAE correspondant à
   l'état sélectionné et à l'état présenté dans la colonne.
4. Le filtre reste exécuté côté serveur et respecte la visibilité autorisée
   des DAE, la pagination et le tri distants.
5. Le bouton « À valider » conserve sa liste actuelle.

## Vérifications

- `make test.unit`
- `make test.api` pour le contrat de la grille et le filtre serveur
- `make test.integration` si la requête ORM ou un finder est modifié
- `make test.style` si des tests sont modifiés
- Recette de la liste courante avec chaque état du filtre ; vérifier aussi la
  liste des archives et consigner si le problème s'y reproduit

## Références

- [ADR 0004 — API Tabulator et patrons de conception](../adr/0004-api-tabulator-et-design-patterns.md)
- [ADR 0014 — Fonctionnalités des grilles Tabulator](../adr/0014-standardisation-fonctionnalites-tabulator.md)
- [ADR 0055 — Workflow de validation des Applicationforms](../adr/0055-workflow-validation-applicationforms.md)
- [Ticket 0036 — Ajouter le compteur des demandes à valider](0036-ajouter-compteur-demandes-a-valider.md)
- `src/Controller/Api/ApplicationformsController.php`
- `webroot/js/views/Applicationforms/applicationform-columns.js`
