# 0002 — Étendre la couverture de tests applicatifs

**Statut :** À planifier
**Priorité :** Haute

## Contexte

La suite PHPUnit est verte avec 107 tests et 301 assertions. Les services et les tables disposent d'une couverture automatisée, et aucun scénario n'est marqué « Non implémenté ».

Les contrôleurs, les parcours HTTP authentifiés, les requêtes réelles des vues SQL et certains cas limites métier restent à couvrir. Le présent ticket regroupe ces travaux sans remettre en cause la stratégie de tests MySQL.

## Objectif

Renforcer la couverture fonctionnelle et mesurer son niveau afin de prévenir les régressions sur les parcours métier et les règles de sécurité.

## Travaux à réaliser

- [x] Créer des tests d'intégration HTTP pour les contrôleurs non couverts, en priorité les API `Applicationforms`, `Users`, `Comments`, `Menus` et `FieldAuthorizations`.
- [x] Couvrir les parcours authentifiés, les refus d'accès par rôle et périmètre de département, ainsi que les réponses d'erreur attendues.
- [x] Ajouter les cas limites métier : recherche FULLTEXT des demandes, bornes haute et double des plages de dates Tabulator, et enchaînement complet du workflow de validation.
- [x] Tester les requêtes des vues SQL `applicationformstatuses`, `currentvalidationroles`, `validation_visas` et `urds` à partir de données créées dans leurs tables sources, sans créer de fixture de vue.
- [x] Vérifier la disponibilité et la compatibilité du moteur de couverture dans le conteneur (`PCOV` en priorité, ou `Xdebug`), sans dégrader l'exécution courante des tests.
- [x] Décider du format de rapport publié : Clover XML pour l'intégration continue et HTML pour la consultation locale ; conserver le chemin de sortie hors des sources versionnées.
- Mesurer la couverture initiale puis appliquer progressivement les seuils suivants, couramment retenus en contexte industriel : 80 % de lignes global, 90 % de lignes pour le code critique (sécurité, autorisation, workflow et services), et un objectif initial de 75 % de branches lorsque cette mesure est disponible.

## Critères d'acceptation

- Chaque contrôleur prioritaire dispose d'au moins un scénario d'accès autorisé et d'un scénario de refus d'accès.
- Les parcours de validation et les vues SQL sont testés sur MySQL `daetf2_test`.
- Le moteur de couverture retenu, son activation et son impact sur les performances sont documentés.
- Les rapports Clover XML et HTML sont générés hors des sources versionnées et sont consultables par les outils concernés.
- Après mesure de l'état initial, les seuils sont documentés puis appliqués progressivement en intégration continue : 80 % de lignes global, 90 % sur le code critique et 75 % de branches lorsque la mesure est disponible.
- Les descriptions TestDox, messages d'assertion et sorties maintenues par le projet sont en français.
- La suite complète reste exécutable avec `make test.all`.

## Références

- [ADR 0051 — Français, langue applicative par défaut](../adr/0051-francais-langue-applicative-par-defaut.md)
- [ADR 0053 — Stratégie de tests unitaires et intégration](../adr/0053-strategie-tests-unitaires-et-integration.md)
- [Configuration PHPUnit](../../phpunit.xml.dist)

## Réalisations complémentaires

- La cible `make test.workflow` exécute les scénarios du workflow de validation et des vues SQL avec la garde MySQL `daetf2_test`.
- La recherche FULLTEXT est testée avec des espaces superflus, plusieurs termes obligatoires et une recherche vide. Sa configuration utilise l'API courante de FriendsOfCake/Search et lie explicitement le paramètre MySQL.
- PCOV 1.0.12 est le moteur retenu. Il est désactivé par défaut et activé uniquement par `PCOV_ENABLED=1` pour les mesures de couverture ; Xdebug reste réservé au débogage.
- `make test.coverage` produit `clover.xml` et `html/index.html` dans un répertoire temporaire unique sous `/tmp` sur l'hôte. La mesure initiale, effectuée le 14 septembre 2026, est de **61,5 % des lignes** (1 630 sur 2 649). PCOV ne fournit pas la couverture de branches dans le rapport Clover (`conditionals=0`) ; l'objectif de 75 % de branches reste donc à réévaluer si Xdebug est retenu ponctuellement pour cette métrique.
