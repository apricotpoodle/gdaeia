# Module Commandes CLI (`src/Command`)

Ce répertoire contient les commandes en ligne de commande (CLI) accessibles via l'exécutable `bin/cake`.

## Commandes disponibles

* **`bin/cake test_email <email>`** : Génère un utilisateur fictif et teste l'expédition d'un courriel transactionnel via `UserMailer` (s'appuie sur `AppMailer::safeSend()`).
* **`bin/cake tree integrity check [--table departments|menus] [--format text|json]`** : Vérifie, sans modifier les données, les relations `parent_id`, les bornes `lft`/`rght` et le niveau lorsque celui-ci est géré par `TreeBehavior`. Un code de sortie non nul signale une incohérence et permet le branchement à une supervision.
* **`bin/cake validation remind`** : Relance les validateurs des étapes échues. L'historique n'avance qu'après le succès de tous les destinataires de l'étape.
* **`bin/cake validation notify-blocked`** : Envoie le récapitulatif des cycles bloqués aux Admins et super-administrateurs autorisés à consulter et outrepasser chaque étape.
* **`bin/cake validation notify-pending`** : Envoie la synthèse quotidienne des DAE en attente, regroupées par destinataire éligible.
* **`bin/cake validation notify-accepted`** : Envoie la synthèse hebdomadaire des DAE acceptées aux Admins qui peuvent lire les demandes incluses.
* **`bin/cake validation repair-orphaned-sequences [--apply]`** : Détecte les références de séquences orphelines et applique uniquement les réalignements uniques avec `--apply`.
* **`bin/cake backlog next <workflow>`** : Recherche le manifeste du workflow dans `docs/backlog/` et affiche le premier ticket dont les dépendances sont terminées.

* **migration_import_departments --source-dir <répertoire> [--dry-run|--apply]** : Prépare ou applique l’import transactionnel des sociétés, services et codes CGR historiques.

## Prochain ticket d'un sous-workflow

Chaque sous-workflow possède un fichier `manifest.json` sous `docs/backlog/`.
La commande résout l'identifiant passé en argument avec la propriété `workflow`
des manifestes, puis respecte l'ordre des tickets et leurs dépendances
`dependsOn`. Elle conserve la compatibilité avec
`bin/cake backlog next validation-af`.

Exemple pour la séquence de finalisation GDAE V3 :

```bash
bin/cake backlog next gdae-v3-completion
```

La commande est en lecture seule. Elle affiche `Aucun ticket exécutable.` si
aucun ticket non terminé n'a toutes ses dépendances au statut `Terminé`, et
signale en français un identifiant de workflow absent.

## Notifications GDAE récurrentes

Exécutez le rappel des étapes échues et la synthèse des demandes en attente
chaque jour à 07 h 00, heure `Europe/Paris`. Le récapitulatif des cycles
bloqués est exécuté à 07 h 30. Le jour et l'heure de la synthèse hebdomadaire
des demandes acceptées sont définis dans Airflow, hors de ce dépôt.

Chaque commande poursuit les envois après un échec, ignore les succès déjà
confirmés et retourne un code non nul si un envoi doit être repris. Les
résumés n'affichent pas les adresses des destinataires.

```cron
0 7 * * * cd /chemin/vers/gdaetf2 && docker compose exec -T gdaetf bin/cake validation remind >> /var/log/gdaetf2-validation-remind.log 2>&1
0 7 * * * cd /chemin/vers/gdaetf2 && docker compose exec -T gdaetf bin/cake validation notify-pending >> /var/log/gdaetf2-validation-pending.log 2>&1
30 7 * * * cd /chemin/vers/gdaetf2 && docker compose exec -T gdaetf bin/cake validation notify-blocked >> /var/log/gdaetf2-validation-blocked.log 2>&1
```

Airflow appelle chaque commande CLI séparément; aucune définition de DAG
n'est fournie ici. Pour un lancement manuel local des deux synthèses :

```bash
make gdae.notifications
```

Cette cible lance `validation notify-pending`, puis
`validation notify-accepted`. Elle ne modifie aucun horaire Airflow.

## Surveillance automatisée des arbres

## Surveillance globale de l’intégrité

Planifiez l’audit global selon la criticité de l’instance. Il n’effectue aucune
réparation ; une anomalie doit être analysée avec le contrôle spécialisé du
domaine concerné.

```cron
15 * * * * cd /chemin/vers/gdaetf2 && docker compose exec -T gdaetf bin/cake integrity audit --format=json >> /var/log/gdaetf2-integrity.log 2>&1
```

Pour une supervision, utilisez le code de sortie et conservez le JSON comme
artefact de diagnostic. Les références et les étapes de validation ne doivent
pas être fusionnées dans une commande de réparation automatique.

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
