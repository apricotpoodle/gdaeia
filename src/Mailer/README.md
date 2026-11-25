# Module Core : Courriels Transactionnels (Mailers)

Ce répertoire contient l'infrastructure d'expédition des courriels de l'application, basée sur le composant `Mailer` natif de CakePHP 5.

## Architecture (DRY & SoC)
Afin de ne pas polluer les contrôleurs avec des logiques de formatage de courriels, et pour éviter la duplication des configurations d'en-têtes (Expéditeur, Format), nous utilisons une architecture par héritage :

1. **`AppMailer.php`** : La classe mère abstraite. Elle configure le socle commun (Adresse d'expédition par défaut, format HTML/Texte fallback). Ne contient aucune méthode métier.
2. **Mailers Métiers (ex: `UserMailer.php`)** : Classes enfants définissant les méthodes spécifiques à un domaine (ex: `forgotPassword`, `welcomeEmail`). Elles injectent les variables (`setViewVars`) et définissent le template à utiliser.

`ValidationWorkflowMailer::validationReminder()` est utilisé par l'action de
relance des cycles bloqués. Le service de workflow détermine au préalable les
validateurs concernés par les étapes bloquées et dédoublonne les destinataires ;
le mailer ne reçoit donc jamais une liste de destinataires non autorisée.

`ApplicationformZeroWorkingDaysNotifier` envoie, après sauvegarde réussie d'une
DAE à zéro jour, une notification individuelle à son auteur, à ses responsables
et aux ADMIN rattachés au département. Il dédoublonne les adresses avant
d'appeler `ValidationWorkflowMailer::zeroWorkingDays()` via `safeSend()`.

`DepartmentDeactivationMailer` envoie le compte rendu de chaque désactivation.
Les responsables, administrateurs du périmètre et le super-administrateur
exécutant reçoivent le rapport complet ; les utilisateurs détachés reçoivent
uniquement l'information concernant leur rattachement. Les adresses d'audit
peuvent être configurées par `DEPARTMENT_DEACTIVATION_AUDIT_RECIPIENTS`.

## Convention d'Utilisation

Les mailers sont appelés via `AppMailer::safeSend()`. Cette méthode journalise
chaque succès et chaque échec dans `logs/email.log`, avec l'action, les
destinataires et le sujet du message. Les messages GDAE expédiés par
`ValidationWorkflowMailer` remplacent les adresses par leur empreinte SHA-256
dans les journaux. Le mailer conserve l'usage de `safeSend()` et retourne
`true` si le transport a accepté le courriel et `false` en cas d'échec SMTP,
sans interrompre le traitement métier.

## Notifications du workflow GDAE

`ValidationWorkflowMailer` prépare les notifications d'étape, les résultats
finaux, les rappels, les synthèses quotidiennes et la synthèse hebdomadaire.
Le service `NotificationDeliveryService` suit chaque envoi par événement et
destinataire : un succès confirmé n'est pas renvoyé, tandis qu'un échec connu
peut être repris. Le registre conserve la clé, le type, l'empreinte du
destinataire, l'état, le nombre de tentatives et les horodatages; il ne
conserve ni adresse en clair ni contenu de DAE.

```php
use App\Mailer\UserMailer;

// ...

$mailer = new UserMailer();
$sent = $mailer->safeSend('forgotPassword', [$user]);
if (!$sent) {
    // Le détail technique est journalisé dans logs/email.log.
}
```
