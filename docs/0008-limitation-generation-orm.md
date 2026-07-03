# ADR 0008 : Stratégie de génération ORM (Contournement CakePHP Bake)

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
La commande `bin/cake bake model all` échoue avec une `DatabaseException` (association mismatch) lors de la rencontre de dépendances circulaires ou bidirectionnelles complexes (ex: `departments` et `cgr_codes`). L'outil tente d'affecter le même alias à des cibles différentes dans le même processus mémoire.

## Décision
La conception de la base de données étant intègre et métier-cohérente, nous n'altérons pas le schéma SQL pour satisfaire un outil de génération. 
La génération automatique des modèles doit se faire séquentiellement via des appels individuels (ex: boucle shell `for table in ... ; do bin/cake bake model $table; done`) pour isoler le cache mémoire de chaque exécution.

### Exception pour les relations bidirectionnelles multiples
Même avec une exécution séquentielle, `bake` crashe définitivement s'il rencontre deux tables liées par de multiples clés étrangères (ex: `departments` et `cgr_codes`). 
Dans ce cas précis, la seule solution fiable consiste à créer la classe `Table` manuellement (ex: `DepartmentsTable.php`) en attribuant des alias uniques et sémantiques aux relations (ex: `OwnedCgrCodes` et `DefaultCgrCode`). Une fois le fichier créé manuellement, la commande `bake` l'ignorera intelligemment et poursuivra la génération du reste de l'ORM.


## Justification
Cette approche respecte la réalité métier du schéma de données tout en palliant une limite technique temporaire de l'outil de génération en ligne de commande de CakePHP.