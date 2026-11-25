# Notifications GDAE Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use
> `superpowers:subagent-driven-development` or
> `superpowers:executing-plans` to implement this plan task-by-task. Steps use
> checkbox (`- [ ]`) syntax for tracking.

**Goal:** Compléter les notifications du ticket 0035 avec des destinataires
conformes au ticket 0030, des commandes réessayables sans renvoi des succès et
une cible Make pour les traitements récurrents.

**Architecture:** Ajouter un registre d’expédition avec clé d’idempotence par
événement, période et destinataire, puis un service partagé qui réclame et
finalise chaque envoi autour de `AppMailer::safeSend()`. Un service de
notifications résout les rôles et périmètres; les commandes existantes et les
contrôleurs délèguent à ces services après la transaction métier.

**Tech Stack:** PHP 8.2+, CakePHP 5.3, ORM CakePHP, MySQL/InnoDB, Phinx,
PHPUnit, Docker Compose, GNU Make.

**Spec:** `docs/superpowers/specs/2026-11-25-ticket-0035-notifications-gdae-design.md`

## Global Constraints

- Employer le français pour les messages, courriels, assertions et descriptions TestDox.
- Utiliser `Europe/Paris` pour les clés de dates et semaines de notification.
- Ne pas créer de DAG Airflow ni appliquer de configuration de production.
- Ne pas modifier de vue SQL; suivre les ADR 0005 et 0006 pour la migration MySQL.
- Ne jamais utiliser `applicationforms.manager_id` pour résoudre un responsable.
- Les Admins destinataires doivent passer les contrôles de visibilité et d’outrepassement effectifs.
- Ne conserver ni adresse courriel en clair ni contenu de DAE dans le registre d’expédition.
- Garder les envois derrière `AppMailer::safeSend()` et ne pas modifier les dépendances.
- Exécuter les commandes PHP, qualité et tests via le `Makefile` racine/Docker.
- Vérifier que `DATABASE_TEST_URL` cible `daetf2_test` avant tout test d’intégration.
- Ne modifier que le ticket 0035; ne pas corriger l’écart `menus.required_permission_id`.
- Avant chaque commit, obtenir l’accord explicite de l’opérateur sur le message complet.

---

## Carte des fichiers

### Dépôt applicatif `app/`

- `config/Migrations/20261125120000_CreateNotificationDeliveries.php` : registre
  SQL de déduplication des envois, sans vue.
- `src/Model/Entity/NotificationDelivery.php` et
  `src/Model/Table/NotificationDeliveriesTable.php` : mapping ORM et
  réclamation atomique d’une livraison.
- `src/Service/NotificationDeliveryService.php` : clé stable, réservation,
  appel du mailer et persistance du résultat.
- `src/Service/Workflow/ValidationWorkflowNotificationService.php` : choix des
  destinataires temps réel/final et appels aux mailers après commit métier.
- `src/Service/Workflow/ApplicationformValidationWorkflow.php` : exposition
  des identifiants de cycle/étape nécessaires à l’idempotence et filtrage des
  Admins pouvant outrepasser une étape.
- `src/Controller/Api/ApplicationformsController.php` : délégation au service
  de notifications; suppression des résolutions de destinataires dans le
  contrôleur.
- `src/Mailer/AppMailer.php` et `src/Mailer/ValidationWorkflowMailer.php` :
  masquage des adresses dans les journaux de notifications GDAE.
- `src/Command/ValidationReminderCommand.php`,
  `src/Command/BlockedValidationDigestCommand.php`,
  `src/Command/ValidationPendingDigestCommand.php` et
  `src/Command/ValidationAcceptedDigestCommand.php` : service partagé, reprise
  des échecs, destinataires et codes de sortie.
- `src/Command/README.md`, `src/Mailer/README.md`,
  `docs/backlog/0035-completer-notifications-gdae.md`,
  `docs/adr/README.md` et `docs/adr/0077-suivi-idempotent-expeditions.md` :
  procédures et décisions.
- `tests/TestCase/Model/Table/NotificationDeliveriesTableTest.php`,
  `tests/TestCase/Service/NotificationDeliveryServiceTest.php`,
  `tests/TestCase/Service/Workflow/ValidationWorkflowNotificationServiceTest.php`,
  tests des commandes et tests API : non-régression au niveau pertinent.

### Dépôt racine

- `Makefile` : cible manuelle `gdae.notifications`, après autorisation et
  création d’une branche dédiée distincte dans le dépôt racine.

## Task 1: Créer le registre de livraisons

**Files:**
- Create: `app/config/Migrations/20261125120000_CreateNotificationDeliveries.php`
- Create: `app/src/Model/Entity/NotificationDelivery.php`
- Create: `app/src/Model/Table/NotificationDeliveriesTable.php`
- Create: `app/tests/Fixture/NotificationDeliveriesFixture.php`
- Create: `app/tests/TestCase/Model/Table/NotificationDeliveriesTableTest.php`
- Create: `app/docs/adr/0077-suivi-idempotent-expeditions.md`
- Modify: `app/docs/adr/README.md`

**Interfaces:**
- Store a 64-character SHA-256 key, notification type, recipient hash, state, attempt count, claim time, last attempt time and sent time.

- [x] **Step 1: Écrire les tests d’intégration de la table.** Vérifier la
  création et la lecture d’un registre, la limite des champs, l’unicité de la
  clé et le fait que le fixture ne contient aucune adresse en clair.

```php
$delivery = $deliveries->newEntity([
    'idempotency_key' => hash('sha256', 'pending:2026-11-25:recipient'),
    'notification_type' => 'pending_digest',
    'recipient_hash' => hash('sha256', 'destinataire@example.test'),
    'state' => 'en_cours',
]);
$this->assertNotFalse($deliveries->save($delivery));
$this->assertSame(64, strlen($delivery->idempotency_key));
```

- [x] **Step 2: Lancer `rtk make test.integration`**. Le nouveau test échoue
  comme attendu sur la table absente; quatre erreurs préexistantes signalent
  `menus.required_permission_id`, laissée hors périmètre.
- [x] **Step 3: Ajouter la migration Phinx et les classes ORM.** Définir une
  clé unique `idempotency_key`, un index d’état/claim, les horodatages et un
  `down()` qui supprime uniquement `notification_deliveries`.
- [x] **Step 4: Garder la table limitée au mapping ORM et à ses validations.**
  La réclamation atomique est implémentée dans la tâche 2 après ses tests
  d’intégration dédiés.
- [x] **Step 5: Relancer `rtk make test.integration`** et vérifier la migration,
  l’index unique et les états de réclamation sur `daetf2_test`. Les tests du
  registre passent; seules trois erreurs connues sur
  `menus.required_permission_id` restent dans la suite globale.
- [ ] **Checkpoint commit:** préparer un message `feat(notifications): créer
  le registre des expéditions`, puis attendre une autorisation explicite
  avant tout commit.

## Task 2: Ajouter le service d’idempotence des courriels

**Files:**
- Create: `app/src/Service/NotificationDeliveryService.php`
- Create: `app/tests/TestCase/Service/NotificationDeliveryServiceTest.php`
- Modify: `app/src/Model/Table/NotificationDeliveriesTable.php`
- Modify: `app/tests/TestCase/Model/Table/NotificationDeliveriesTableTest.php`

**Interfaces:**
- `NotificationDeliveriesTable::claim(string $key, string $type, string $recipientHash, int $leaseSeconds): ?NotificationDelivery`.
- `NotificationDeliveriesTable::isSent(string $key): bool` et `complete(NotificationDelivery $delivery, bool $sent): void`.
- `NotificationDeliveryService::sendOnce(string $type, string $eventKey, User $recipient, Closure $send): bool`.
- Construire la clé avec SHA-256 de `type`, `eventKey` et adresse normalisée; ne conserver que son empreinte.
- Retourner `true` pour un succès confirmé ou déjà confirmé; `false` pour un échec ou une claim active afin que l’orchestrateur puisse réessayer.

- [x] **Step 1: Écrire un test qui vérifie l’échec SMTP.** Le registre doit
  porter `echec`, augmenter `attempt_count` et conserver seulement une
  empreinte du destinataire.

```php
$sent = $service->sendOnce(
    'pending_digest',
    '2026-11-25',
    new User(['email' => 'destinataire@example.test']),
    static fn(): bool => false,
);
$this->assertFalse($sent);
$this->assertSame('echec', $deliveries->find()->firstOrFail()->state);
```
- [x] **Step 2: Écrire les tests de réclamation, succès et bail expiré.** Une
  claim active doit être refusée; un échec ou un bail expiré peut être réclamé
  une nouvelle fois; une livraison envoyée doit toujours être ignorée.
- [x] **Step 3: Exécuter `rtk make test.integration`** pour vérifier que le
  nouveau test échoue sur le service manquant. Ce service accède au registre
  MySQL; le test ne doit donc pas être classé unitaire.
- [x] **Step 4: Écrire les tests du succès répété, de la reprise d’échec, de
  l’expiration du bail et de la collision de clé.** Vérifier qu’un succès déjà
  confirmé ne rappelle pas la fermeture `$send`.
- [x] **Step 5: Implémenter les méthodes de table puis `sendOnce()`.** Réclamer la ligne, appeler `$send`
  hors transaction, puis passer à `envoyee` ou `echec`. Incrémenter les
  tentatives à chaque claim et borner le bail à 15 minutes.

```php
$delivery = $this->deliveries->claim($key, 900);
if ($delivery === null) {
    return $this->deliveries->isSent($key);
}
$sent = $send();
$this->deliveries->complete($delivery, $sent);

return $sent;
```
- [x] **Step 6: Relancer `rtk make test.integration`** et vérifier qu’un échec
  connu reste retentable et qu’un succès ne l’est pas.
- [ ] **Checkpoint commit:** préparer un message `feat(notifications):
  rendre les envois réessayables`, puis attendre l’autorisation explicite.

## Task 3: Filtrer les destinataires et livrer les transitions temps réel

**Files:**
- Create: `app/src/Service/Workflow/ValidationWorkflowNotificationService.php`
- Create: `app/tests/TestCase/Service/Workflow/ValidationWorkflowNotificationServiceTest.php`
- Modify: `app/src/Service/Workflow/ApplicationformValidationWorkflow.php`
- Modify: `app/src/Controller/Api/ApplicationformsController.php`
- Modify: `app/src/Mailer/AppMailer.php`
- Modify: `app/src/Mailer/ValidationWorkflowMailer.php`
- Modify: `app/tests/TestCase/Mailer/AppMailerTest.php`
- Modify: `app/tests/TestCase/Controller/Api/ApplicationformsControllerTest.php`
- Modify: `app/tests/TestCase/Controller/Api/ApplicationformWorkflowAuthorizationTest.php`

**Interfaces:**
- `ValidationWorkflowNotificationService::notifyStart(Applicationform $applicationform, ValidationWorkflowRun $run): int`.
- `ValidationWorkflowNotificationService::notifyVote(Applicationform $applicationform, int $runId, int $votedStepId, string $state, bool $final, ?string $comment): int`.
- `ValidationWorkflowNotificationService::collectFinalAcceptanceRecipients(Applicationform $applicationform): list<User>`.
- `ValidationWorkflowNotificationService::collectCurrentStepRecipients(Applicationform $applicationform, int $runId): list<User>`.
- Chaque méthode appelle `NotificationDeliveryService::sendOnce()` avec une clé d’événement stable basée sur le cycle, l’étape et le destinataire.
- Ajouter à `AppMailer::safeSend()` une option booléenne nommée `$redactRecipients`, fausse par défaut; `ValidationWorkflowMailer` l’active par défaut pour ses courriels.

- [x] **Step 1: Écrire les tests de destinataires.** Couvrir un rôle de
  validation normal, l’étape bloquée avec Admin visible et autorisé à
  outrepasser, l’Admin sans habilitation, le super-administrateur hors
  périmètre et la déduplication d’une adresse répétée.
- [x] **Step 2: Écrire le test d’acceptation finale.** Attendre l’auteure,
  tous les managers associés, les Admins qui peuvent consulter la DAE, et
  aucun utilisateur déduit de `applicationforms.manager_id`.

```php
$recipients = $notifications->collectFinalAcceptanceRecipients($form);
$this->assertSame(
    ['assistante@example.test', 'manager@example.test', 'admin@example.test'],
    array_map(static fn(User $user): string => $user->email, $recipients),
);
```
- [x] **Step 3: Écrire le test de journalisation.** Appeler le mailer avec
  `$redactRecipients = true` et vérifier que l’adresse brute n’apparaît pas
  dans le journal, mais que l’action, le sujet et une empreinte sont traçables.
- [x] **Step 4: Lancer `rtk make test.api`** et confirmer les échecs initiaux des
  nouvelles assertions API; ne pas modifier de schema pour contourner
  `menus.required_permission_id`.
- [x] **Step 5: Implémenter le service de notification.** Utiliser
  `ApplicationformValidationWorkflow`, `ApplicationformPolicy` et
  `PermissionResolver` pour résoudre les rôles et les accès; dédupliquer par
  adresse normalisée; réserver la livraison avec des clés dérivées de
  `runId`, `stepId`, état et destinataire.
- [x] **Step 6: Déplacer les boucles d’envoi hors du contrôleur.** Après le
  retour de la transaction de `start()`, appeler `notifyStart()` avec le cycle
  retourné. Après `vote()`, ajouter `run_id` à la réponse métier et appeler
  `notifyVote()` avec le cycle, l’étape votée, l’état, le caractère final et
  le commentaire; ne pas envoyer dans la transaction. Conserver les
  destinataires actuels du refus final, avec déduplication et journalisation
  masquée.
- [x] **Step 7: Ajouter l’option de masquage dans `AppMailer::safeSend()`.**
  Garder le comportement historique par défaut pour les autres mailers et
  l’activer pour tous les envois GDAE de `ValidationWorkflowMailer`.
- [x] **Step 8: Relancer `rtk make test.api`, `rtk make test.integration`,
  `rtk make test.workflow`, `rtk make cs.check` et `rtk make stan`.** Vérifier
  les tests d’autorisation et le contenu des journaux.
- [ ] **Checkpoint commit:** proposer un message complet de type
  `feat(notifications): notifier les transitions GDAE`; attendre l’accord
  avant commit.

## Task 4: Corriger les destinataires des synthèses

**Files:**
- Modify: `app/src/Service/Workflow/ApplicationformValidationWorkflow.php`
- Modify: `app/src/Command/ValidationPendingDigestCommand.php`
- Modify: `app/src/Command/ValidationAcceptedDigestCommand.php`
- Modify: `app/src/Command/BlockedValidationDigestCommand.php`
- Modify: `app/tests/TestCase/Command/BlockedValidationDigestCommandTest.php`
- Create: `app/tests/TestCase/Command/ValidationPendingDigestCommandTest.php`
- Create: `app/tests/TestCase/Command/ValidationAcceptedDigestCommandTest.php`

**Interfaces:**
- `collectPendingDigest()` renvoie une entrée par utilisateur avec les DAE triées et dédoublonnées.
- Ajouter `collectAcceptedDigest(\Cake\I18n\DateTime $from, \Cake\I18n\DateTime $to)` qui renvoie une entrée par Admin et uniquement les DAE qu’il peut lire.
- `collectAcceptedDigest()` reçoit les bornes de période dans le fuseau applicatif et les clés de livraison hebdomadaires utilisent `o-W`.
- Chaque commande garde l’injection de collecteur/expéditeur utilisée pour ses tests et délègue chaque envoi à `NotificationDeliveryService::sendOnce()`.

- [x] **Step 1: Ajouter le test quotidien par destinataire.** Vérifier la
  période locale `Y-m-d`, l’ordre `begin_at/id`, l’exclusion des comptes
  supprimés et la réussite sans destinataire.

```php
$digests = $workflow->collectPendingDigest();
$this->assertSame(
    [10, 12],
    array_map(static fn(Applicationform $form): int => (int)$form->id, $digests[0]['applicationforms']),
);
```
- [x] **Step 2: Ajouter le test de synthèse hebdomadaire.** Créer deux
  départements et deux Admins de périmètres différents; chaque Admin ne reçoit
  que les DAE des services qu’il peut consulter. Vérifier la semaine ISO et
  que la clé de succès est indépendante par destinataire.

```php
$digests = $workflow->collectAcceptedDigest($weekStart, $weekEnd);
$this->assertSame([10], array_map(
    static fn(Applicationform $form): int => (int)$form->id,
    $digests[0]['applicationforms'],
));
```
- [x] **Step 3: Ajouter le test du digest bloqué.** Seuls les Admins/superusers
  autorisés à lire et à outrepasser les étapes du cycle reçoivent ce digest.
- [x] **Step 4: Exécuter `rtk make test.unit`** et vérifier les nouveaux échecs de
  commande avant d’implémenter les filtres.
- [x] **Step 5: Remplacer les marqueurs globaux `notification.*.last_run`**
  par les clés par destinataire/période du service partagé. Calculer les clés
  avec `DateTime::now()` dans le fuseau applicatif `Europe/Paris`.
- [x] **Step 6: Continuer après un échec de destinataire.** Les commandes
  tentent les autres envois, affichent les comptes de réussites/échecs/absents
  sans courriel en clair et retournent `CODE_ERROR` si un échec reste à
  reprendre.
- [x] **Step 7: Relancer `rtk make test.unit`, `rtk make test.integration`,
  `rtk make test.workflow`, `rtk make cs.check` et `rtk make stan`.** Les cas ORM/workflow
  doivent utiliser exclusivement `daetf2_test`.
- [ ] **Checkpoint commit:** proposer un message complet de type
  `fix(notifications): filtrer les synthèses GDAE`; attendre l’accord.

## Task 5: Rendre les commandes de relance réessayables

**Files:**
- Modify: `app/src/Command/ValidationReminderCommand.php`
- Modify: `app/src/Service/Workflow/ApplicationformValidationWorkflow.php`
- Modify: `app/tests/TestCase/Command/ValidationReminderCommandTest.php`
- Modify: `app/tests/TestCase/Service/ApplicationformValidationWorkflowTest.php`

**Interfaces:**
- La collecte retourne les identifiants de cycle/étape, la DAE et les destinataires sans enregistrer `last_reminded_at` avant l’envoi.
- La clé par destinataire combine `validation_reminder`, l’identifiant d’étape et `reminder_count + 1`; les succès déjà enregistrés sont ignorés.

- [x] **Step 1: Écrire un test d’échec partiel.** Pour deux destinataires,
  simuler succès puis échec; exécuter une seconde fois et vérifier que seul le
  destinataire en échec est retenté.

```php
$command->execute($args, $io);
$command->execute($args, $io);
$this->assertSame(1, $sendCounts['premier@example.test']);
$this->assertSame(2, $sendCounts['second@example.test']);
```
- [x] **Step 2: Écrire un test sans destinataire et un test d’étape non échue.**
  Vérifier qu’aucun courriel n’est construit et que l’horodatage d’une étape
  n’est pas avancé avant le résultat des envois.
- [x] **Step 3: Lancer `rtk make test.unit`** et vérifier que les tests échouent
  sur l’écriture anticipée actuelle de `last_reminded_at`.
- [x] **Step 4: Modifier la collecte pour être en lecture seule.** Après les
  tentatives, enregistrer `last_reminded_at` et `reminder_count` seulement
  lorsque les destinataires de l’étape ont tous un succès confirmé; laisser
  les échecs retentables par le registre.
- [x] **Step 5: Relancer `rtk make test.unit`, `rtk make test.workflow`,
  `rtk make cs.check` et `rtk make stan`.** Confirmer qu’une exécution répétée ne
  renvoie pas les succès confirmés.
- [ ] **Checkpoint commit:** préparer le message complet et attendre l’accord
  explicite avant commit.

## Task 6: Ajouter la commande Make et la documentation opératoire

**Files:**
- Modify: `Makefile` racine, après accord pour créer une branche dans le dépôt racine.
- Modify: `app/src/Command/README.md`
- Modify: `app/src/Mailer/README.md`
- Modify: `app/docs/backlog/0035-completer-notifications-gdae.md`

**Interfaces:**
- Ajouter `make gdae.notifications`, qui lance `validation notify-pending` puis `validation notify-accepted` dans le conteneur CakePHP.
- Airflow appelle séparément les commandes CLI; la cible Make ne crée ni ne modifie d’ordonnancement.

- [x] **Step 1: Demander l’autorisation pour la branche racine.** Le dépôt
  racine est propre sur `main`; proposer `feature/0035-notifications-gdae`,
  vérifier qu’elle est libre, puis attendre l’accord avant de la créer.
- [x] **Step 2: Ajouter `gdae.notifications` au `.PHONY`, à l’aide Make et à la
  section commandes applicatives.** Chaque recette Docker utilise
  `$(PHP_SERVICE_NAME)` et `-u www-data`, selon les cibles existantes.

```make
gdae.notifications:
	@docker compose exec -u www-data $(PHP_SERVICE_NAME) bin/cake validation notify-pending
	@docker compose exec -u www-data $(PHP_SERVICE_NAME) bin/cake validation notify-accepted
```

- [x] **Step 3: Exécuter `rtk make -n gdae.notifications`.** Vérifier que la
  sortie contient les deux commandes CLI prévues et ne démarre aucun conteneur.
- [x] **Step 4: Documenter le lancement quotidien de `validation notify-pending`
  à 07 h 00 Europe/Paris, l’horaire hebdomadaire Airflow non fixé dans le
  dépôt et l’usage manuel de `make gdae.notifications` en environnement
  local.
- [x] **Step 5: Mettre à jour les cases de travail et vérifications du ticket
  0035**, puis exécuter `rtk git -C app diff --check` et `rtk git diff --check`.
- [ ] **Checkpoint commit:** séparer les commits `app/` et racine; demander
  l’autorisation explicite avec le message complet de chaque dépôt.

## Task 7: Vérification et livraison du ticket

**Files:**
- Review: tous les fichiers modifiés pour le ticket 0035 dans `app/` et le dépôt racine.

**Interfaces:**
- Aucun nouveau comportement n’est ajouté dans cette tâche; elle rassemble les résultats de vérification, le diff et les limites connues.

- [x] **Step 1: Vérifier les deux branches actives et les deux états Git.**
  S’assurer qu’aucun fichier étranger au ticket n’est indexé ou modifié.
- [x] **Step 2: Lancer `rtk make test.unit`, `rtk make test.api`,
  `rtk make test.workflow`, `rtk make test.integration`, `rtk make test.style`,
  `rtk make cs.check` et `rtk make stan`
  selon les fichiers modifiés.** N’exécuter `rtk make test.all` que si les risques
  transversaux le justifient et vérifier préalablement `DATABASE_TEST_URL`.
- [x] **Step 3: Rapporter exactement les résultats.** Si une commande échoue
  sur l’absence connue de `menus.required_permission_id` dans `daetf2_test`,
  signaler le blocage sans corriger cet écart.
- [ ] **Step 4: Présenter les branches, commits autorisés, fichiers modifiés et
  commandes vérifiées.** Ne pousser, fusionner ou créer aucune PR sans
  confirmation distincte.
