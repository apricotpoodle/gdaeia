# ADR 0053 : Stratégie de tests et MySQL dédié à l’intégration

## 1. Contexte

L’application dispose de squelettes CakePHP pour les tables ORM, mais ceux-ci ne constituent pas une couverture effective tant qu’ils sont marqués incomplets. Les règles métier critiques sont également réparties entre entités, politiques, services, contrôleurs et tables ORM. Il faut rendre le niveau de test explicite pour que la suite soit rapide, fiable et utile en revue.

## 2. Décision

Nous distinguons trois niveaux complémentaires :

* Les **tests unitaires** n’accèdent ni à MySQL, ni au réseau. Ils ciblent les entités, politiques et services déterministes. Les collaborateurs externes sont doublés par des mocks ou stubs.
* Les **tests d’intégration ORM** utilisent les fixtures CakePHP et la base de tests reconstruite par les migrations. Ils valident les règles de validation, contraintes, associations, finders et requêtes.
* Les **tests fonctionnels HTTP** utilisent `IntegrationTestTrait` pour vérifier les statuts, autorisations, réponses JSON et parcours contrôleur/API.

Chaque correction de bug ajoute d’abord un test de non-régression au niveau le plus bas qui reproduit fidèlement le défaut. Toute règle d’accès ou de filtrage de données doit comporter un cas autorisé et un cas refusé. Les tests doivent décrire un résultat métier observable, plutôt qu’une méthode interne.

Les descriptions TestDox, messages d’assertion et messages de tests incomplets relevant du projet sont en français. Les cibles Make exportent `APP_DEFAULT_LOCALE=fr_FR` lors de l’exécution afin que CakePHP produise ses messages localisables dans la langue retenue par l’équipe.

Les tests unitaires sont exécutés en premier. La suite complète est ensuite exécutée dans un environnement identifié comme base de tests, car son bootstrap applique les migrations et peut reconstruire son schéma.

### Moteur de la base de tests d’intégration

Les tests d’intégration ORM et fonctionnels HTTP utilisent une instance **MySQL dédiée aux tests**, de même famille et de même version majeure que l’environnement applicatif. Sa connexion est fournie par la variable d’environnement `DATABASE_TEST_URL` et cible une base explicitement nommée comme base de test (par exemple `gdaetf2_test`).

SQLite n’est pas retenu pour cette suite d’intégration. L’application dépend de fonctionnalités MySQL non portables : index `FULLTEXT` créé par les migrations et recherche `MATCH ... AGAINST` en mode booléen. Un passage à SQLite produirait une suite verte sans garantir le comportement de production, ou imposerait des branches spécifiques à SQLite dans les migrations et le code métier.

La suite complète est exécutée depuis le conteneur PHP, qui embarque le pilote `pdo_mysql`. Avant toute exécution, l’opérateur doit vérifier que `DATABASE_TEST_URL` ne désigne ni la base de développement, ni une base de recette ou de production. Le bootstrap de test est autorisé à créer, modifier et reconstruire uniquement le schéma de cette base dédiée.

### Moteur de couverture de code

Les rapports de couverture utilisent **PCOV**. Il est installé dans l’image PHP, mais désactivé par défaut (`pcov.enabled=0`) afin de préserver les performances des commandes ordinaires, notamment `make test.all`. Les commandes de couverture l’activent explicitement avec `PCOV_ENABLED=1` et désactivent Xdebug avec `XDEBUG_MODE=off` : ces deux moteurs ne doivent pas mesurer la couverture simultanément.

Xdebug reste installé et dédié au débogage interactif. PHPUnit génère le rapport Clover XML destiné à l’intégration continue et le rapport HTML destiné à la consultation locale, dans un répertoire temporaire hors des sources versionnées. La commande `make test.coverage` ouvre automatiquement le rapport HTML avec le navigateur système et supprime avant chaque mesure les répertoires de couverture âgés de plus de 24 heures. La cible `make test.coverage.clean` permet une suppression immédiate à la demande.

### Intégration continue

GitHub Actions est la première forge retenue pour exécuter l'intégration continue, car le dépôt applicatif y est déjà hébergé et contient un workflow `.github/workflows/ci.yml`. Ce workflow doit reproduire les contraintes de cette décision : MySQL dédié, PCOV pour la couverture, rapports Clover XML et HTML en artefacts, et absence de SQLite pour les tests d'intégration. Les choix de commandes, formats de rapports et variables d'environnement restent portables afin de permettre une migration ultérieure vers Codeberg CI/Actions ou une solution équivalente.

## 3. Conséquences

**Positives :** séparation claire des responsabilités, retours rapides sur les règles métier, fidélité des tests d’intégration au moteur de production, protection renforcée des ACL et des périmètres, diagnostic plus simple en CI et mesures de couverture plus rapides grâce à PCOV.

**Négatives :** quelques mocks sont nécessaires aux services reposant sur `TableRegistry`; les tests ORM et HTTP restent plus lents, nécessitent une base MySQL dédiée et une configuration explicite de ses accès. PCOV ajoute une extension et impose une image PHP reconstruite après chaque mise à jour de sa version.
