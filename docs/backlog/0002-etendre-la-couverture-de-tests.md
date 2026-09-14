# 0002 — Étendre la couverture de tests applicatifs

**Statut :** À planifier
**Priorité :** Haute

## Contexte

La suite PHPUnit est verte avec 88 tests et 250 assertions. Les services et les tables disposent d'une couverture automatisée, et aucun scénario n'est marqué « Non implémenté ».

Les contrôleurs, les parcours HTTP authentifiés, les requêtes réelles des vues SQL et certains cas limites métier restent à couvrir. Le présent ticket regroupe ces travaux sans remettre en cause la stratégie de tests MySQL.

## Objectif

Renforcer la couverture fonctionnelle et mesurer son niveau afin de prévenir les régressions sur les parcours métier et les règles de sécurité.

## Travaux à réaliser

- Créer des tests d'intégration HTTP pour les contrôleurs non couverts, en priorité les API `Applicationforms`, `Users`, `Comments`, `Menus` et `FieldAuthorizations`.
- Couvrir les parcours authentifiés, les refus d'accès par rôle et périmètre de département, ainsi que les réponses d'erreur attendues.
- Ajouter les cas limites métier : recherche FULLTEXT des demandes, bornes haute et double des plages de dates Tabulator, et enchaînement complet du workflow de validation.
- Tester les requêtes des vues SQL `applicationformstatuses`, `currentvalidationroles`, `validation_visas` et `urds` à partir de données créées dans leurs tables sources, sans créer de fixture de vue.
- Générer un rapport de couverture PHPUnit et proposer un seuil minimal progressif, adapté à l'outillage PHP disponible dans le conteneur.

## Critères d'acceptation

- Chaque contrôleur prioritaire dispose d'au moins un scénario d'accès autorisé et d'un scénario de refus d'accès.
- Les parcours de validation et les vues SQL sont testés sur MySQL `daetf2_test`.
- La couverture est publiée sous une forme consultable et son seuil est documenté puis appliqué en intégration continue.
- Les descriptions TestDox, messages d'assertion et sorties maintenues par le projet sont en français.
- La suite complète reste exécutable avec `make test.all`.

## Références

- [ADR 0051 — Français, langue applicative par défaut](../adr/0051-francais-langue-applicative-par-defaut.md)
- [ADR 0053 — Stratégie de tests unitaires et intégration](../adr/0053-strategie-tests-unitaires-et-integration.md)
- [Configuration PHPUnit](../../phpunit.xml.dist)
