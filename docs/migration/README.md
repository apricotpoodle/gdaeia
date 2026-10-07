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
