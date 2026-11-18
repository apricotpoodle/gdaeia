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
- Formaliser la validation locale avant partage sur MySQL dédié et mesurer la couverture avec PCOV. Aucun seuil ne bloque l'intégration tant qu'une CI distante n'est pas à nouveau retenue.

La réalisation détaillée de ce dernier volet est planifiée dans le [ticket 0004 — Validation locale MySQL et couverture](0004-mettre-en-place-une-integration-continue-mysql.md).

## Critères d'acceptation

- Chaque contrôleur prioritaire dispose d'au moins un scénario d'accès autorisé et d'un scénario de refus d'accès.
- Les parcours de validation et les vues SQL sont testés sur MySQL `daetf2_test`.
- Le moteur de couverture retenu, son activation et son impact sur les performances sont documentés.
- Les rapports Clover XML et HTML sont générés hors des sources versionnées et sont consultables localement.
- La mesure de couverture est documentée ; les seuils sont différés jusqu'à une décision ultérieure sur leur automatisation.
- La procédure de validation locale exécute la suite contre MySQL dédié avant partage.
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
- `make test.coverage` produit `clover.xml` et `html/index.html` dans un répertoire temporaire unique sous `/tmp` sur l'hôte. Les rapports âgés de plus de 24 heures sont purgés au lancement ; `make test.coverage.clean` permet leur suppression immédiate. L'option `BROWSER=true` évite l'ouverture interactive du rapport.
- La mesure du 18 novembre 2026, lancée avec `make -C .. test.coverage BROWSER=true`, atteint **81,60 % des lignes** (7 862 sur 9 635), contre 61,5 % (1 630 sur 2 649) le 14 septembre, soit +20,1 points. La couverture des fonctions et méthodes est de 67,05 % (582 sur 868). La suite complète a réussi avec **622 tests et 2 296 assertions** ; les rapports ont été générés dans `/tmp/daetf2-coverage.FkHwoj`. PCOV ne fournit pas la couverture de branches dans Clover (`conditionals=0`) ; l'objectif de 75 % de branches reste à réévaluer si Xdebug est retenu ponctuellement pour cette métrique.
- Le diagnostic ciblé `make -C .. test.coverage.policies` a exécuté avec succès **53 tests et 206 assertions**, mais échoue au seuil actuel de 100 % des lignes pour six policies : `ValidationCircuitManagerPolicy` (0/3), `PermissionPolicy` (4/5), `DepartmentPolicy` (27/30), `UserPolicy` (41/54), `ValidationCircuitRulePolicy` (7/19) et `ApplicationformPolicy` (131/150).
- Les lacunes prioritaires du rapport global concernent le workflow et les autorisations : `ValidationWorkflowIntegrityChecker` (0/43 lignes) et `ValidationWorkflowOrphanScanner` (0/5) ne sont pas couverts ; `PermissionsTable` est à 6/18, `WorkflowSettingsTable` à 4/11, `AuthorizationTargetsTable` à 5/13, `PermissionScopesTable` à 4/10 et `RolePermissionsTable` à 11/23. `FieldAuthorizationService` atteint 35/46 lignes (76,1 %) et le service `ApplicationformValidationWorkflow` 347/393 (88,3 %). La couverture du groupe `src/Service/` est de 83,6 % (1 840/2 202) et celle des fichiers liés au workflow de 82,7 % (1 087/1 315). Trois autres composants d'intégrité restent entièrement non couverts : `CommentsIntegrityChecker` (0/42), `TreeIntegrityCheckAdapter` (0/16) et `ViewsIntegrityChecker` (0/24).
