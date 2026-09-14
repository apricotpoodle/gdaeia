# ADR 0053 : Stratégie de tests et MySQL dédié à l’intégration

## 1. Contexte

L’application dispose de squelettes CakePHP pour les tables ORM, mais ceux-ci ne constituent pas une couverture effective tant qu’ils sont marqués incomplets. Les règles métier critiques sont également réparties entre entités, politiques, services, contrôleurs et tables ORM. Il faut rendre le niveau de test explicite pour que la suite soit rapide, fiable et utile en revue.

## 2. Décision

Nous distinguons trois niveaux complémentaires :

* Les **tests unitaires** n’accèdent ni à MySQL, ni au réseau. Ils ciblent les entités, politiques et services déterministes. Les collaborateurs externes sont doublés par des mocks ou stubs.
* Les **tests d’intégration ORM** utilisent les fixtures CakePHP et la base de tests reconstruite par les migrations. Ils valident les règles de validation, contraintes, associations, finders et requêtes.
* Les **tests fonctionnels HTTP** utilisent `IntegrationTestTrait` pour vérifier les statuts, autorisations, réponses JSON et parcours contrôleur/API.

Chaque correction de bug ajoute d’abord un test de non-régression au niveau le plus bas qui reproduit fidèlement le défaut. Toute règle d’accès ou de filtrage de données doit comporter un cas autorisé et un cas refusé. Les tests doivent décrire un résultat métier observable, plutôt qu’une méthode interne.

Les tests unitaires sont exécutés en premier. La suite complète est ensuite exécutée dans un environnement identifié comme base de tests, car son bootstrap applique les migrations et peut reconstruire son schéma.

### Moteur de la base de tests d’intégration

Les tests d’intégration ORM et fonctionnels HTTP utilisent une instance **MySQL dédiée aux tests**, de même famille et de même version majeure que l’environnement applicatif. Sa connexion est fournie par la variable d’environnement `DATABASE_TEST_URL` et cible une base explicitement nommée comme base de test (par exemple `gdaetf2_test`).

SQLite n’est pas retenu pour cette suite d’intégration. L’application dépend de fonctionnalités MySQL non portables : index `FULLTEXT` créé par les migrations et recherche `MATCH ... AGAINST` en mode booléen. Un passage à SQLite produirait une suite verte sans garantir le comportement de production, ou imposerait des branches spécifiques à SQLite dans les migrations et le code métier.

La suite complète est exécutée depuis le conteneur PHP, qui embarque le pilote `pdo_mysql`. Avant toute exécution, l’opérateur doit vérifier que `DATABASE_TEST_URL` ne désigne ni la base de développement, ni une base de recette ou de production. Le bootstrap de test est autorisé à créer, modifier et reconstruire uniquement le schéma de cette base dédiée.

## 3. Conséquences

**Positives :** séparation claire des responsabilités, retours rapides sur les règles métier, fidélité des tests d’intégration au moteur de production, protection renforcée des ACL et des périmètres, et diagnostic plus simple en CI.

**Négatives :** quelques mocks sont nécessaires aux services reposant sur `TableRegistry`; les tests ORM et HTTP restent plus lents, nécessitent une base MySQL dédiée et une configuration explicite de ses accès.
