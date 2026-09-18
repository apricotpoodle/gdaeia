# Module Commandes CLI (`src/Command`)

Ce répertoire contient les commandes en ligne de commande (CLI) accessibles via l'exécutable `bin/cake`.

## Commandes disponibles

* **`bin/cake test_email <email>`** : Génère un utilisateur fictif et teste l'expédition d'un courriel transactionnel via `UserMailer` (s'appuie sur `AppMailer::safeSend()`).
* **`bin/cake tree integrity check [--table departments|menus] [--format text|json]`** : Vérifie, sans modifier les données, les relations `parent_id`, les bornes `lft`/`rght` et le niveau lorsque celui-ci est géré par `TreeBehavior`. Un code de sortie non nul signale une incohérence et permet le branchement à une supervision.
* **`bin/cake validation remind`** : Relance les validateurs des étapes échues, au plus une fois par étape et par période de vingt-quatre heures.

## Relances de validation

Planifiez la commande chaque heure : son idempotence évite les doublons de courriel sur une période de vingt-quatre heures.

```cron
0 * * * * cd /chemin/vers/gdaetf2 && docker compose exec -T gdaetf bin/cake validation remind >> /var/log/gdaetf2-validation-remind.log 2>&1
```

## Surveillance automatisée des arbres

La commande envoie une alerte détaillée lorsque le diagnostic échoue. Configurez sur chaque instance les variables non sensibles suivantes dans le fichier `.env` racine :

```dotenv
APP_INSTANCE_NAME=gdaetf2-production
TREE_INTEGRITY_ALERT_RECIPIENT=infogestion@lemonde.fr
```

Docker transmet automatiquement le nom de la machine hôte via `APP_HOST_HOSTNAME`. Le courriel contient ce nom, le nom du conteneur, l’horodatage, les identifiants concernés et la commande de diagnostic à relancer. Une erreur d’envoi est tracée dans `logs/email.log` sans masquer l’échec d’intégrité.

Avant de planifier la surveillance, vérifiez explicitement la configuration, sans interroger la base :

```bash
make tree.check.config
```

Pour valider le destinataire, le relais SMTP et le rendu du courriel sans créer d’incohérence, envoyez un message dont le sujet et le contenu sont marqués **TEST** :

```bash
make tree.alert.test
```

Exemple cron, toutes les quinze minutes :

```cron
*/15 * * * * cd /chemin/vers/gdaetf2 && make tree.check >> /var/log/gdaetf2-tree-check.log 2>&1
```

Dans Airflow, utilisez `make tree.check` comme commande de l’opérateur de tâche. Son code de sortie est non nul en cas d’incohérence, ce qui permet à Airflow de signaler également l’échec de la tâche.

## Différences Dev / Prod
* **Dev** : Courriels interceptés localement par Mailpit (`http://localhost:8025`).
* **Prod** : Routage vers le serveur SMTP officiel via la configuration `app_local.php` / `.env`.

## ADR Associés
* [ADR 0043 : Commande CLI de test d'envoi de courriels et serveur Mailpit](../../docs/adr/0043-commande-test-envoi-email.md)
