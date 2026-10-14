# Migration depuis l’ancienne base DAE

Cette documentation décrit le premier lot de migration : utilisateurs et
habilitations. Le DDL de référence est conservé dans
[`ancienne-base-ddl.sql`](ancienne-base-ddl.sql), et les extractions SQL sont
dans [`export-habilitations.sql`](export-habilitations.sql).

## Principe

Les requêtes produisent des jeux de données intermédiaires CSV en UTF-8. Elles
doivent être exécutées avec un client SQL puis exportées avec leurs en-têtes.
Le fichier CSV est ensuite contrôlé avant tout import dans l’application
courante.

## Utilisation avec Docker

Le client MySQL historique est isolé dans `docker-compose.migration.yml`. Il
ne démarre pas avec l’application et son conteneur est supprimé après chaque
commande.

Configurer les variables `LEGACY_DB_*` dans le `.env` racine, puis vérifier la
connexion :

```bash
make migration.connection-check
```

Pour exporter automatiquement les résultats :

```bash
make migration.export
```

La commande lit chaque `SELECT` identifié dans `export-habilitations.sql`,
exécute les requêtes séparément via PDO et écrit un fichier CSV UTF-8 par
requête dans `app/tmp/migration/exports`. Les mots de passe et jetons sont
refusés par le script même s’ils étaient ajoutés par erreur au SQL.

Enfin, contrôler les fichiers :

```bash
make migration.quality
```

Les rapports sont écrits dans `app/tmp/migration/reports`. Aucun de ces deux
répertoires ne doit être versionné.

Pour construire les référentiels historiques à rapprocher des départements
actuels :

```bash
make migration.mapping
```

La commande écrit :

- `app/tmp/migration/mappings/old-departments.csv` : sociétés et services
  historiques, avec une proposition de rapprochement (`rapprochement_propose`)
  et un commentaire indiquant le contrôle restant à effectuer ; les
  identifiants cibles restent vides tant que le référentiel courant n’est pas
  fourni ;
- `app/tmp/migration/mappings/cgr-department-correspondence.csv` : couples
  société/service/CGR dédoublonnés, avec les deux colonnes de départements
  cibles laissées vides.

Ces fichiers sont des supports de rapprochement. Ils ne créent encore aucun
département et ne modifient aucune table de la nouvelle application.

## Contrôle local des CSV

Après l’export, contrôler les trois fichiers principaux sans accéder à MySQL :

```bash
docker compose exec -T gdaetf bin/cake migration_quality /chemin/vers/exports
```

La commande vérifie les fichiers utilisateurs, rôles et rôles contextualisés,
signale les courriels absents ou dupliqués ainsi que les liens orphelins. Elle
refuse tout fichier contenant un mot de passe ou un jeton. Elle ne modifie ni
le répertoire source ni la base applicative.

Pour produire les rapports CSV de qualité dans un répertoire séparé :

```bash
docker compose exec -T gdaetf bin/cake migration_quality /chemin/vers/exports \
    --report-dir /chemin/vers/rapports
```

Les rapports sont des résultats de contrôle, pas des fichiers d’import : les
doublons et les correspondances ambiguës doivent être arbitrés avant le lot 2.

Les identifiants source (`USR_ID`, `RLE_ID`, etc.) sont conservés pour le
rapprochement, mais ne doivent pas être injectés directement dans les tables
cibles.

## Correspondances principales

| Ancienne base | Application courante | Règle |
| --- | --- | --- |
| `ts_user_usr.USR_ADR` | `users.email` | Adresse normalisée, unique |
| `ts_user_usr.USR_NOM` | `users.username` | Facultatif, à dédoublonner |
| `ts_user_usr.USR_PRENOM` | `users.firstname` | Copie nettoyée |
| `ts_user_usr.USR_PATRONYME` | `users.lastname` | Copie nettoyée |
| `ts_user_usr.RLE_ID` | `users.role_id` | Correspondance par code de rôle |
| `tj_stn_usr_rle_sur` | `user_departments` | Conversion société/service à valider |
| `tj_gru_user_gru` | — | À traduire en rôles ou périmètres |
| `tj_usr_srv_visible_usv` | `user_departments` | Conversion selon l’arbre courant |
| `tj_cgr_srv_usr_csu` | `user_departments` | Information de périmètre à réduire |

L’ancienne base peut affecter plusieurs rôles à un utilisateur selon une
société. L’application courante possède un rôle principal par utilisateur et
des départements explicites. Les cas non représentables doivent être placés
dans un rapport de rapprochement manuel, jamais perdus silencieusement.

## Mots de passe

`users.password` est obligatoire dans l’application courante. L’import ne doit
pas recopier `USR_PWD` sans preuve de compatibilité avec le hasher courant.
Pour le premier lot, créer un hash technique aléatoire inutilisable, puis
demander aux utilisateurs de passer par « Mot de passe oublié ». Le flux
existant envoie un jeton temporaire et applique ensuite le hash courant lors de
la définition du nouveau mot de passe.

Ne jamais exporter `USR_PWD`, `USR_TOKEN_CONF`, `USR_TOKEN_REG` ou
`USR_TOKEN_REM` dans les CSV d’import.

## Contrôles avant import

- comparer le nombre de lignes source et exportées ;
- détecter les courriels vides, invalides ou dupliqués ;
- vérifier les rôles source sans correspondance cible ;
- vérifier les utilisateurs sans périmètre ;
- produire les correspondances société/service vers `departments.id` ;
- tester l’idempotence sur un jeu de données de recette ;
- supprimer les fichiers contenant d’éventuels mots de passe avant archivage.
