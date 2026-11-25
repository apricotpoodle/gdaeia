# Spécification — Ticket 0035, notifications GDAE

**Date :** 25 novembre 2026
**Statut :** À relire avant le plan d’implémentation
**Périmètre :** ticket applicatif 0035 uniquement

## Contexte observé

Les mailers et les commandes CLI de notifications existent déjà. Le démarrage
et la progression du workflow envoient déjà des courriels, mais les règles de
destinataires ne couvrent pas toutes les décisions du ticket 0030. La commande
hebdomadaire sélectionne actuellement tous les Admins actifs, et non seulement
ceux qui peuvent lire les services concernés. Plusieurs commandes valident une
période globale même si certains envois ont échoué. La relance des étapes
échues marque une relance avant l’envoi SMTP.

Les ADR acceptés 0033, 0034 et 0036 établissent l’usage des mailers métier,
`EmailLoggerTrait` et `AppMailer::safeSend()`. L’ADR 0055 définit les étapes et
leurs destinataires; l’ADR 0063 centralise l’autorisation d’outrepasser une
étape bloquante. L’ADR 0071 impose `Europe/Paris` comme fuseau applicatif par
défaut. Les ADR 0005 et 0006 encadrent une éventuelle migration MySQL; aucune
vue SQL ne doit être modifiée pour ce ticket. L’ADR 0058 réserve
`workflow_settings` au paramétrage global et ne convient pas au suivi des
tentatives d’expédition.

## Objectifs et règles métier

1. À chaque transition du workflow, notifier les personnes responsables de
   l’étape nouvellement active. Quand une étape est bloquée au sens du service
   de workflow, inclure les Admins/super-administrateurs qui peuvent réellement
   l’outrepasser et consulter la DAE.
2. À l’acceptation finale, notifier une seule fois l’assistante auteure, les
   responsables associés au service et les utilisateurs de rôle Admin qui
   peuvent consulter la DAE. Ignorer `applicationforms.manager_id`.
3. Envoyer chaque jour la liste des DAE en attente aux personnes éligibles aux
   rôles des étapes concernées. Trier les demandes par date comme le finder
   existant; le lancement planifié est à 07 h 00, heure applicative.
4. Envoyer chaque semaine les DAE acceptées à chaque Admin qui peut consulter
   le service de chaque DAE incluse. La date et l’heure hebdomadaires restent
   configurées par l’exploitation dans Airflow.
5. Préserver les commandes actuelles de rappel des étapes échues et de
   synthèse des cycles bloqués. Leur état d’expédition doit aussi empêcher un
   doublon lorsqu’elles sont relancées et permettre de réessayer un échec.
6. Fournir une cible Make manuelle pour lancer les traitements récurrents de
   notification. L’application ne crée ni DAG ni configuration Airflow et
   n’applique aucune configuration de production.

Les destinataires doivent être actifs, posséder une adresse exploitable et
être dédoublonnés par adresse normalisée. Les Admins sont filtrés selon les
Policies et habilitations effectives; un simple `role_id` ne prouve pas qu’ils
peuvent consulter une DAE ou outrepasser une étape.

## Architecture proposée

### Résolution des événements et des destinataires

Conserver `ApplicationformValidationWorkflow` comme source des transitions,
des étapes bloquées et des personnes éligibles à une étape. Ajouter un service
de notification de workflow qui prépare et expédie les courriels après la
transaction métier, à partir des identifiants stables du cycle et de l’étape.
Les contrôleurs API ne calculent pas eux-mêmes les rôles ni les destinataires.

Le résultat final accepté utilise explicitement les trois groupes du ticket
0030 : auteure, responsables du service et Admins visibles. Le refus final
conserve les destinataires métier actuellement utilisés tant qu’aucune autre
règle n’a été décidée. Les listes périodiques réutilisent les finders et
services du workflow plutôt que de recopier les règles d’éligibilité.

### Suivi durable des envois

Créer une table dédiée `notification_deliveries` par migration Phinx. Elle
conserve, pour chaque clé logique de notification et destinataire normalisé,
le type, l’état (`en_cours`, `envoyee` ou `echec`), le nombre de tentatives,
les horodatages de tentative et de succès. Une contrainte unique sur la clé
logique rend l’enregistrement atomique entre deux exécutions concurrentes.
Les clés comprennent le type d’événement, l’identifiant du cycle/étape ou la
période locale (`YYYY-MM-DD` ou semaine ISO), et une empreinte de l’adresse
normalisée.

Le corps du message, les commentaires de validation et les adresses en clair
ne sont pas stockés dans cette table. Le canal de courriels reste celui des
ADR 0034 et 0036; pour les notifications GDAE, `safeSend()` journalise une
empreinte du destinataire au lieu de son adresse en clair. Les lignes
`envoyee` sont ignorées lors d’une relance; les lignes `echec` sont retentées.
Une notification sans destinataire est un résultat vide explicite, sans ligne
de livraison ni tentative SMTP.

Un état `en_cours` doit être réclamé atomiquement et expirer après une durée
bornée afin qu’un processus interrompu ne bloque pas indéfiniment la reprise.
Les échecs sont consignés sans arrêter les envois des autres destinataires;
une commande CLI retourne un code non nul si au moins un envoi échoue.
L’expédition SMTP ne fournit pas de garantie exactement-une-fois lorsqu’un
processus s’arrête après acceptation SMTP mais avant de persister le succès;
le suivi évite les doublons des envois confirmés et permet la reprise des
échecs connus.

La migration ajoute une table applicative ordinaire, sans vue SQL. Ajouter un
ADR applicatif proposé, numéroté après l’ADR 0076, pour consigner le suivi
idempotent des expéditions.

### Commandes et exploitation

Conserver les noms CLI actuels `validation remind`, `validation notify-blocked`,
`validation notify-pending` et `validation notify-accepted`. Ces commandes
utilisent le service de livraison partagé et exposent un résumé des réussites,
échecs et destinataires absents sans afficher le contenu des DAE.

Ajouter une cible Make manuelle qui appelle les commandes de notifications
périodiques. Documenter les commandes CLI destinées à Airflow, l’exécution
quotidienne de `notify-pending` à 07 h 00 en `Europe/Paris`, et le fait que le
calendrier hebdomadaire de `notify-accepted` relève d’Airflow. La cible Make
sert au diagnostic et aux exécutions manuelles; elle ne modifie aucun
ordonnancement externe.

## Flux et erreurs

1. Le service métier valide puis persiste la transition du workflow.
2. Après validation de la transaction, le service de notification résout les
   destinataires autorisés et construit une clé d’idempotence stable par
   événement et destinataire.
3. Le service réclame la livraison, appelle `safeSend()`, puis persiste le
   succès ou l’échec. Une ligne de succès n’est jamais réexpédiée pour la même
   clé.
4. Les commandes récurrentes parcourent tous les destinataires même si un
   envoi échoue, puis renvoient un code d’erreur agrégé pour permettre à
   Airflow de signaler l’échec.
5. Les absences d’adresses, listes vides et échecs SMTP sont traçables. Pour
   les notifications GDAE, les journaux et la table de suivi ne contiennent ni
   adresse en clair, ni corps de courriel, ni commentaire ou détail métier de
   DAE; les journaux utilisent l’empreinte du destinataire.

Les clés de période sont calculées avec l’horloge applicative
`Europe/Paris`, conformément à l’ADR 0071. L’heure d’exécution reste du ressort
de l’orchestrateur.

## Vérifications prévues

- Tests unitaires du service de livraison : clés stables, destinataire
  dédoublonné, succès non renvoyé, échec réessayé, processus `en_cours` expiré,
  envoi sans destinataire, traitement après un échec partiel et absence
  d’adresse en clair dans les journaux GDAE.
- Tests d’intégration ORM sur la table, l’index unique, les transactions de
  réclamation et les sélecteurs de rôles/périmètres sur MySQL.
- Tests du workflow et des commandes : notification à l’étape suivante,
  Admins pouvant outrepasser une étape bloquée, destinataires de l’acceptation,
  synthèses quotidiennes/hebdomadaires et codes de sortie en cas d’échec.
- Tests de contrat Make/documentation et tests de style si des tests sont
  ajoutés.
- Vérifications applicatives prévues par `app/AGENTS.md` : `make test.unit`,
  `make cs.check`, `make stan`, `make test.integration`; `make test.api` si les
  contrôleurs changent, `make test.workflow` si le workflow change, et
  `make test.style` si les tests sont modifiés.

L’environnement de test connu peut échouer sur l’absence de
`menus.required_permission_id` dans `daetf2_test`. Ce défaut reste hors
périmètre; tout échec lié à cette anomalie sera rapporté sans modifier le
schéma correspondant.

## Fichiers et livrables envisagés

- `app/src/Service/Workflow/` : résolution et expédition des notifications.
- `app/src/Model/Table/` et `app/src/Model/Entity/` : suivi des livraisons.
- `app/src/Command/` et `app/src/Application.php` : reprises, erreurs et
  commandes CLI existantes.
- `app/config/Migrations/` : table dédiée, sans altération de vue SQL.
- `app/tests/` : tests unitaires, intégration ORM et API/commandes selon les
  fichiers touchés.
- `app/src/Command/README.md`, `app/src/Mailer/README.md`, le ticket 0035 et
  l’index ADR applicatif : documentation locale et nouvel ADR proposé.
- `Makefile` racine : cible de lancement manuel.

## Revue de cohérence

- La règle d’acceptation reprend les trois groupes métier du 0030 et exclut
  `applicationforms.manager_id`.
- La règle de synthèse hebdomadaire vérifie la visibilité par DAE et ne repose
  pas sur une liste globale d’Admins.
- L’état d’expédition est séparé des paramètres `workflow_settings` selon
  l’ADR 0058.
- Les commandes et mailers existants sont réutilisés; Airflow reste externe.
- La migration ne modifie aucune vue SQL et les tests d’intégration restent
  limités à `daetf2_test`.
- Les seules modifications prévues couvrent le ticket 0035.
