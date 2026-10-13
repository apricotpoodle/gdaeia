# Déploiement de GDAETF2 en préproduction sur `websrv30`

## Objectif

Ce document décrit les étapes nécessaires pour implanter l’application GDAETF2
sur le serveur interne `websrv30` dans des conditions proches de la production.
Il concerne l’infrastructure Docker située à la racine du projet et
l’application CakePHP située dans `app/`.

Le dépôt actuel fournit principalement un environnement de développement. Il
ne faut donc pas démarrer la préproduction avec la configuration locale sans
la contrôler et l’adapter.

## Décision de transfert : Git ou FileZilla ?

Le déploiement recommandé se fait par SSH, avec une version applicative
identifiée par un commit ou un tag :

- Git est recommandé si `websrv30` peut accéder au dépôt ;
- `rsync` sur SSH est recommandé pour transférer une livraison préparée ;
- FileZilla peut être utilisé ponctuellement en SFTP, mais n’est pas recommandé
  comme mécanisme principal de déploiement.

FileZilla est manuel et ne garantit pas facilement la reproductibilité de la
livraison. Il peut oublier des fichiers cachés, modifier les permissions ou
laisser un transfert incomplet. Git ou `rsync` permettent de mieux contrôler la
version livrée, les exclusions et la traçabilité.

## 1. Prérequis du serveur

Valider avec l’équipe système la présence et les versions supportées de :

- Docker Engine ;
- Docker Compose v2 ;
- GNU Make ;
- Git ;
- `rsync`, `openssh-client`, `flock` et `curl` ;
- un accès réseau à MySQL ;
- un accès au relais SMTP officiel ;
- un certificat TLS correspondant au nom DNS de préproduction.

Le serveur doit également disposer :

- d’un compte technique dédié, par exemple `gdaetf` ;
- d’un accès Docker limité aux personnes ou services autorisés ;
- de règles pare-feu adaptées ;
- d’un reverse-proxy ou d’une publication de ports validée ;
- d’une rotation des logs ;
- d’une supervision de l’URL HTTPS et du conteneur applicatif.

## 2. Arborescence cible

L’application seule ne suffit pas. Il faut transférer l’infrastructure racine
et le dépôt applicatif imbriqué :

```text
/srv/gdaetf2/
├── .env
├── docker-compose.yml
├── Makefile
├── .dockerfile/
└── app/
```

Le fichier `.env` doit être créé directement sur le serveur ou transmis par un
mécanisme sécurisé. Il ne doit pas être versionné.

Les dépendances front-end locales doivent être transférées par Git avec
l’application. Elles sont stockées sous `app/webroot/assets/`, notamment dans
`assets/luxon/`, `assets/tabulator/` et `assets/treeselectjs/`. Vérifier avant
chaque livraison qu’elles sont suivies par Git et qu’aucune règle `vendor/` ne
les ignore.

## 3. Transférer une version identifiée

### Option Git

Lorsque les dépôts sont accessibles depuis `websrv30` :

```bash
git clone <depot-infrastructure> /srv/gdaetf2
git -C /srv/gdaetf2/app clone <depot-application> .
git -C /srv/gdaetf2/app checkout <commit-ou-tag-valide>
```

Si l’infrastructure et l’application sont déjà présentes, vérifier la version
avant toute opération :

```bash
git -C /srv/gdaetf2 status --short
git -C /srv/gdaetf2/app status --short
git -C /srv/gdaetf2/app log -1 --oneline --decorate
```

Le déploiement doit partir d’un commit ou d’un tag validé, et non d’un état de
travail local non identifié.

### Option `rsync` sur SSH

Depuis une machine de préparation :

```bash
rsync -aHAX --delete \
  --exclude='.env' \
  --exclude='tmp/' \
  --exclude='logs/' \
  /chemin/gdaetf2/ \
  gdaetf@websrv30:/srv/gdaetf2/
```

Effectuer une sauvegarde de la livraison précédente avant d’utiliser
`--delete`. Vérifier également que la commande ne transfère aucun secret,
fichier temporaire, log ou certificat privé non prévu.

## 4. Préparer la configuration préproduction

Créer un `.env` spécifique à la préproduction. Ne pas copier le `.env` de
développement et ne pas reprendre automatiquement `.env.example.websrv30`.

Les variables à définir incluent notamment :

```dotenv
DEBUG=false
APP_DEFAULT_LOCALE=fr_FR
APP_FULL_BASE_URL=https://<nom-dns-preproduction>
APP_INSTANCE_NAME=gdaetf2-preproduction

DB_CONNECTION=mysql
DB_HOST=<hote-mysql-preproduction>
DB_PORT=3306
DB_DATABASE=<base-preproduction>
DB_USERNAME=<utilisateur-applicatif>
DB_PASSWORD=<mot-de-passe-fourni-securisement>
DATABASE_URL="mysql://${DB_USERNAME}:${DB_PASSWORD}@${DB_HOST}:${DB_PORT}/${DB_DATABASE}?encoding=utf8&timezone=UTC&cacheMetadata=true&quoteIdentifiers=false&persistent=false"

# Réservée aux tests et distincte de la préproduction.
DATABASE_TEST_URL="mysql://<utilisateur-test>:<mot-de-passe-test>@<hote-mysql>/daetf2_test?encoding=utf8&timezone=UTC&cacheMetadata=true&quoteIdentifiers=false&persistent=false"

MAIL_MAILER=smtp
MAIL_HOST=<relais-smtp-preproduction>
MAIL_PORT=<port-smtp>
MAIL_USERNAME=<identifiant-smtp-si-necessaire>
MAIL_PASSWORD=<mot-de-passe-smtp-si-necessaire>
MAIL_ENCRYPTION=<mode-tls-si-necessaire>
MAIL_FROM_ADDRESS=<adresse-expediteur-validee>
MAIL_FROM_NAME="GDAETF2 préproduction"

TREE_INTEGRITY_ALERT_RECIPIENT=<adresse-de-supervision>
SECURITY_SALT=<secret-aleatoire-unique>
SECURITY_CIPHERSEED=<secret-aleatoire-unique>
```

Générer les secrets sur une machine de confiance, sans les commiter :

```bash
openssl rand -hex 32
openssl rand -hex 16
```

Le fichier `.env.example.websrv30` actuellement présent dans le workspace
contient des valeurs sensibles. Ces valeurs doivent être considérées comme
compromises et remplacées. Les identifiants MySQL, mots de passe SMTP et clés
CakePHP ne doivent pas être réutilisés.

Protéger le fichier sur le serveur :

```bash
chown gdaetf:gdaetf /srv/gdaetf2/.env
chmod 600 /srv/gdaetf2/.env
```

## 5. Adapter l’infrastructure Docker

Le `docker-compose.yml` actuel est orienté développement. Avant son utilisation
en préproduction, valider une configuration dédiée ou une surcharge qui :

- supprime le service Mailpit ;
- désactive ou retire Xdebug et PCOV ;
- désactive DebugKit ;
- n’utilise pas les certificats `localhost` ;
- utilise le certificat TLS correspondant au nom DNS de préproduction ;
- limite les ports publiés aux flux nécessaires ;
- conserve les versions verrouillées par `composer.lock` ;
- limite les volumes montés à ceux nécessaires au fonctionnement ;
- conserve les permissions d’exécution de `www-data` ;
- définit une politique de rotation et de conservation des logs.

Ne pas utiliser une image ou une dépendance `latest` sans validation préalable.
La version effectivement déployée doit être reproductible.

## 6. Préparer la base de données

La base de préproduction doit être distincte :

- de la base de développement ;
- de `daetf2_test` ;
- de la production.

Faire valider par le DBA :

- la création de la base et sa collation ;
- l’utilisateur applicatif et ses droits minimaux ;
- l’accès depuis le réseau Docker ;
- les index FULLTEXT ;
- les vues SQL ;
- les sauvegardes et la restauration ;
- la fenêtre autorisée pour les migrations.

Les vues SQL sont gérées manuellement dans les migrations conformément aux
ADR 0005 et 0006. Ne pas recréer le schéma par génération automatique.

## 7. Construire et démarrer l’application

Depuis le répertoire racine :

```bash
cd /srv/gdaetf2
make config
make init
make composer.install
make ps
```

Vérifier l’état et les journaux :

```bash
docker compose ps
docker compose logs --tail=200 gdaetf
```

## 8. Appliquer les migrations

Après validation de la connexion à la base préproduction, appliquer les
migrations une seule fois pour la livraison :

```bash
cd /srv/gdaetf2
make db.migrate
```

Ne jamais mettre `make db.migrate` sous cron. Une migration doit être revue,
exécutée pendant une fenêtre contrôlée et enregistrée dans le compte rendu de
déploiement.

## 9. Vérifications applicatives

Vérifier la configuration des alertes sans interroger les données :

```bash
cd /srv/gdaetf2
make tree.check.config
```

Puis effectuer les contrôles suivants :

```bash
make tree.check
docker compose exec -T -u www-data gdaetf bin/cake
curl -f https://<nom-dns-preproduction>/
docker compose logs --tail=200 gdaetf
```

Contrôler manuellement :

- l’accès HTTPS et la redirection éventuelle depuis HTTP ;
- l’authentification ;
- les autorisations et les périmètres de données ;
- les routes Web et API ;
- l’envoi SMTP ;
- la génération PDF ;
- l’écriture dans `logs/` et `tmp/` ;
- l’absence d’affichage d’erreurs PHP ;
- l’absence d’accès à DebugKit ;
- le retour correct des commandes cron.

Après une livraison, vider les caches si nécessaire :

```bash
make cache.clear
docker compose restart gdaetf
```

## 10. Commandes à installer sous cron

Les tâches suivantes sont définies par les commandes CakePHP documentées dans
`app/src/Command/README.md`.

Créer d’abord les fichiers de logs et les permissions adaptées. Utiliser un
compte ayant accès au socket Docker, ou un mécanisme d’exécution validé par
l’équipe système.

Exemple de crontab :

```cron
SHELL=/bin/sh
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin

0 * * * * flock -n /run/lock/gdaetf2-validation-remind.lock sh -c 'cd /srv/gdaetf2 && /usr/bin/docker compose exec -T -u www-data gdaetf bin/cake validation remind' >> /var/log/gdaetf2-validation-remind.log 2>&1

30 7 * * * flock -n /run/lock/gdaetf2-validation-blocked.lock sh -c 'cd /srv/gdaetf2 && /usr/bin/docker compose exec -T -u www-data gdaetf bin/cake validation notify-blocked' >> /var/log/gdaetf2-validation-blocked.log 2>&1

*/15 * * * * flock -n /run/lock/gdaetf2-tree-check.lock sh -c 'cd /srv/gdaetf2 && /usr/bin/docker compose exec -T -u www-data gdaetf bin/cake tree integrity check' >> /var/log/gdaetf2-tree-check.log 2>&1
```

### Signification des tâches

| Fréquence | Commande | Fonction |
| --- | --- | --- |
| Chaque heure | `validation remind` | Relance les validateurs des étapes échues, au plus une fois par étape et par période de 24 heures. |
| Chaque jour à 07 h 30 | `validation notify-blocked` | Envoie le récapitulatif des cycles de validation bloqués. |
| Toutes les 15 minutes | `tree integrity check` | Contrôle les arbres `departments` et `menus` et envoie une alerte en cas d’incohérence. |

Ne pas planifier automatiquement :

- `make db.migrate` ;
- `make composer.install` ;
- `make cache.clear` ;
- `make test.all` ;
- `make tree.alert.test` ;
- `bin/cake test_email`.

Ces commandes sont réservées aux livraisons, à la maintenance ou aux tests
explicitement demandés.

### Opérations système complémentaires

À définir séparément avec l’équipe système :

- sauvegarde MySQL quotidienne et test périodique de restauration ;
- rotation des fichiers `/var/log/gdaetf2-*.log` ;
- supervision de l’URL HTTPS ;
- supervision de l’état du conteneur ;
- alerte sur les codes de retour non nuls des tâches cron.

La commande de sauvegarde MySQL dépend de la politique DBA. Elle doit utiliser
un fichier d’identifiants protégé, par exemple `/etc/mysql/backup.cnf` en
`chmod 600`, et ne doit pas exposer le mot de passe dans la crontab ou dans les
journaux.

## 11. Tests avant validation

Avant l’ouverture aux utilisateurs, exécuter depuis un environnement dont
`DATABASE_TEST_URL` pointe exclusivement vers `daetf2_test` :

```bash
make test.unit
make cs.check
make stan
make test.integration
make test.workflow
make test.api
make test.all
```

Les tests d’intégration ne doivent jamais utiliser la base de préproduction ni
la base de production. SQLite ne doit pas être utilisé pour valider les
comportements MySQL spécifiques, notamment les index FULLTEXT et les vues SQL.

## 12. Retour arrière

Conserver pour chaque livraison :

- le commit précédemment déployé ;
- le commit nouvellement déployé ;
- la liste des migrations exécutées ;
- la configuration `.env` stockée de manière sécurisée ;
- les sauvegardes MySQL ;
- les journaux du déploiement.

Un retour arrière du code ne suffit pas si une migration irréversible a déjà
été appliquée. Toute restauration de base doit être validée par le DBA.

## 13. Validation finale

La mise en service peut être considérée comme prête lorsque :

- le commit déployé est identifié et validé ;
- les secrets sont propres à la préproduction ;
- `DEBUG=false` est confirmé ;
- la base et le SMTP sont ceux de la préproduction ;
- les migrations sont appliquées et enregistrées ;
- les contrôles HTTPS, authentification, autorisation et API réussissent ;
- les courriels de test ont été validés avec les destinataires appropriés ;
- les trois tâches cron sont installées et testées ;
- les sauvegardes, logs et alertes sont opérationnels ;
- la procédure de retour arrière est connue.

## Points à confirmer avant exécution

- nom DNS HTTPS de la préproduction ;
- emplacement final de l’application, proposé ici : `/srv/gdaetf2` ;
- dépôt Git et commit ou tag à livrer ;
- serveur MySQL, base et comptes préproduction ;
- relais SMTP et adresse d’expédition ;
- certificat TLS ;
- compte technique et politique d’accès Docker ;
- stratégie de sauvegarde et de supervision ;
- validation d’un Compose dédié à la préproduction.
