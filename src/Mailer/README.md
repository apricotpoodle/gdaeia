# Module Core : Courriels Transactionnels (Mailers)

Ce répertoire contient l'infrastructure d'expédition des courriels de l'application, basée sur le composant `Mailer` natif de CakePHP 5.

## Architecture (DRY & SoC)
Afin de ne pas polluer les contrôleurs avec des logiques de formatage de courriels, et pour éviter la duplication des configurations d'en-têtes (Expéditeur, Format), nous utilisons une architecture par héritage :

1. **`AppMailer.php`** : La classe mère abstraite. Elle configure le socle commun (Adresse d'expédition par défaut, format HTML/Texte fallback). Ne contient aucune méthode métier.
2. **Mailers Métiers (ex: `UserMailer.php`)** : Classes enfants définissant les méthodes spécifiques à un domaine (ex: `forgotPassword`, `welcomeEmail`). Elles injectent les variables (`setViewVars`) et définissent le template à utiliser.

## Convention d'Utilisation

Les mailers sont appelés via `AppMailer::safeSend()`. Cette méthode journalise
chaque succès et chaque échec dans `logs/email.log`, avec l'action, les
destinataires et le sujet du message. Elle retourne `true` si le transport a
accepté le courriel et `false` en cas d'échec SMTP, sans interrompre le
traitement métier.

```php
use App\Mailer\UserMailer;

// ...

$mailer = new UserMailer();
$sent = $mailer->safeSend('forgotPassword', [$user]);
if (!$sent) {
    // Le détail technique est journalisé dans logs/email.log.
}
```
