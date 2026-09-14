# 0004 — Mettre en place une intégration continue MySQL et couverture

**Statut :** À planifier
**Priorité :** Haute
**Estimation :** 3 à 6 heures ; premier pipeline vert attendu en 2 à 3 heures.

## Contexte

La suite locale est verte avec 107 tests et 301 assertions. Elle utilise MySQL `daetf2_test`, car l'application dépend d'un index `FULLTEXT`, de `MATCH ... AGAINST` en mode booléen et de vues SQL de workflow. La couverture initiale est de 61,5 % des lignes (1 630 sur 2 649), mesurée avec PCOV 1.0.12.

Le workflow existant `.github/workflows/ci.yml` provient du squelette CakePHP. Il cible SQLite, configure `coverage: none`, ne publie aucun rapport et sa matrice contient deux entrées PHP 8.5 identiques. Il est donc incompatible avec l'ADR 0053 et ne valide pas fidèlement l'application.

## Objectif

Fournir un pipeline reproductible sur la forge retenue — GitHub Actions, Codeberg CI/Actions ou solution équivalente — qui valide chaque proposition de modification avec MySQL, génère les rapports de couverture et applique des seuils progressivement.

## Décisions et contraintes à respecter

- Le fichier d'implémentation initial est `app/.github/workflows/ci.yml`. Si Codeberg ou une autre forge est retenue, adapter la syntaxe du pipeline et la disponibilité des images/actions, sans changer les exigences fonctionnelles.
- Le pipeline ne dépend pas du `Makefile` ni du `Dockerfile` de la racine, car `app/` est un dépôt Git autonome. Il configure PHP, Composer et MySQL directement dans le workflow.
- Utiliser MySQL, de même version majeure que l'environnement applicatif. Vérifier cette version avant de la figer dans l'image de service CI.
- Créer une base exclusivement dédiée, nommée `daetf2_test`, et fournir `DATABASE_TEST_URL` sans afficher ses identifiants dans les journaux.
- Ne jamais utiliser SQLite dans la CI d'intégration : il ne couvre ni `FULLTEXT`, ni `MATCH ... AGAINST`, ni les vues SQL MySQL.
- Inclure `pdo_mysql`, `mbstring` et `intl` dans l'environnement PHP. Aligner la version PHP de référence sur le conteneur local (actuellement PHP 8.3.33, donc série 8.3) avant d'envisager une matrice de compatibilité.
- Utiliser PCOV pour la couverture (`coverage: pcov` ou installation équivalente). Ne pas activer Xdebug simultanément. PCOV est plus rapide mais ne remonte pas la couverture de branches dans Clover.
- Exécuter PHPUnit avec `APP_DEFAULT_LOCALE=fr_FR`, afin que les sorties propres au projet restent en français.
- Les migrations doivent construire le schéma de la base CI, y compris l'index FULLTEXT et les vues SQL. Vérifier explicitement les tests FULLTEXT et `ValidationWorkflowViewsTest`.
- Les fixtures peuvent reconstruire les tables de test ; la base CI ne doit contenir aucune donnée persistante utile.
- Le `SECURITY_SALT` actuellement écrit en clair dans le workflow doit être remplacé par une valeur de test non sensible, fournie par variable d'environnement ou secret de forge. Ne pas réutiliser de clé locale ou de production.
- Éviter l'affichage de `DATABASE_TEST_URL`, mots de passe, clés ou fichiers `.env` dans les logs de CI.

## Travaux à réaliser

1. [x] Choisir la forge d'exécution initiale : **GitHub Actions**. Le dépôt applicatif est déjà hébergé sur GitHub et contient le workflow `.github/workflows/ci.yml`. Conserver une conception portable vers Codeberg CI/Actions ou une solution équivalente.
2. Reprendre les déclencheurs : `pull_request`, lancement manuel et `push` sur les branches réellement utilisées, y compris les branches de fonctionnalités si elles doivent être contrôlées avant une demande de fusion.
3. Simplifier la matrice PHP : supprimer le doublon PHP 8.5 et établir un job de référence PHP 8.3. Les essais sur versions minimale/maximale ne seront conservés que s'ils sont compatibles avec les dépendances et apportent une valeur explicite.
4. Ajouter un service MySQL isolé, sa vérification de disponibilité et la création de `daetf2_test` avec les droits minimaux nécessaires.
5. Configurer les variables de test non secrètes et lancer la suite PHPUnit complète sur MySQL. Vérifier que les migrations, fixtures, index FULLTEXT et vues SQL sont créés dans la base de test.
6. Configurer PCOV, produire Clover XML et HTML hors des sources versionnées, puis publier les deux sorties comme artefacts téléchargeables de l'exécution CI.
7. Conserver la vérification PHP_CodeSniffer et PHPStan. Corriger la configuration PHP et les extensions si les outils l'exigent.
8. Définir une politique de seuils progressive : partir d'un seuil global au plus égal à la mesure initiale de 61,5 %, documenter sa hausse vers 80 %, et exiger 90 % pour le périmètre critique une fois celui-ci objectivement défini.
9. Décider la politique de branches : accepter l'absence de cette métrique avec PCOV, ou créer un job Xdebug séparé, plus lent, réservé à la mesure des branches. Ne pas exiger artificiellement 75 % sans mesure disponible.
10. Documenter la forge, les commandes exécutées, les artefacts, les variables attendues, les seuils actifs et la procédure de diagnostic d'un échec.

## Critères d'acceptation

- Un `push`, une demande de fusion et un lancement manuel déclenchent le pipeline sur la forge retenue.
- La suite PHPUnit est exécutée sur MySQL `daetf2_test`, jamais SQLite, et termine sans erreur.
- Les tests FULLTEXT et le workflow/vues SQL sont exécutés avec succès dans la CI.
- PCOV est le moteur de couverture du job standard ; Xdebug n'est pas activé dans ce job.
- Les rapports `clover.xml` et HTML sont produits et téléchargeables comme artefacts de l'exécution.
- Aucun secret, URL de connexion complète ni contenu de `.env` n'apparaît dans les journaux.
- Les seuils réellement activés sont documentés, contrôlés par le pipeline et n'empêchent pas arbitrairement l'adoption progressive vers 80 % global et 90 % critique.
- La décision sur la couverture de branches est documentée.
- La configuration de qualité (PHP_CodeSniffer et PHPStan) reste verte.
- Le ticket 0002 référence cette mise en œuvre et peut être clôturé lorsque les seuils et le pipeline sont opérationnels.

## Décision prise

GitHub Actions est retenu comme première cible d'intégration continue. Ce choix s'appuie sur l'hébergement actuel du dépôt et sur la présence d'un workflow existant. Le pipeline ne doit pas dépendre d'une fonctionnalité propriétaire non indispensable : une migration ultérieure vers Codeberg CI/Actions ou une forge équivalente doit rester possible en adaptant seulement la syntaxe et les mécanismes d'artefacts.

## Risques et vérifications secondaires

- Une image MySQL d'une version majeure différente peut modifier les contraintes, la syntaxe SQL ou le comportement FULLTEXT ; vérifier l'alignement avant d'industrialiser.
- Les vues SQL doivent être créées dans la base de test, sans fixtures de vue : seuls leurs tables sources sont alimentées.
- L'exécution de couverture augmente le temps du pipeline et la mémoire consommée ; mesurer ce coût, conserver PCOV et éviter de faire échouer les contrôles courants sur un rapport HTML.
- Les artefacts HTML peuvent être volumineux ; définir une durée de rétention adaptée à la forge.
- Les scripts Actions et les versions d'actions doivent être maintenus et épinglés selon la politique de sécurité de l'équipe.

## Références

- [Ticket 0002 — Étendre la couverture de tests applicatifs](0002-etendre-la-couverture-de-tests.md)
- [ADR 0053 — Stratégie de tests unitaires et intégration](../adr/0053-strategie-tests-unitaires-et-integration.md)
- [Workflow CI actuel](../../.github/workflows/ci.yml)
- [Configuration PHPUnit](../../phpunit.xml.dist)
