# Module Core : Courriels Transactionnels (Mailers)

Ce répertoire contient l'infrastructure d'expédition des courriels de l'application, basée sur le composant `Mailer` natif de CakePHP 5.

## Architecture (DRY & SoC)
Afin de ne pas polluer les contrôleurs avec des logiques de formatage de courriels, et pour éviter la duplication des configurations d'en-têtes (Expéditeur, Format), nous utilisons une architecture par héritage :

1. **`AppMailer.php`** : La classe mère abstraite. Elle configure le socle commun (Adresse d'expédition par défaut, format HTML/Texte fallback). Ne contient aucune méthode métier.
2. **Mailers Métiers (ex: `UserMailer.php`)** : Classes enfants définissant les méthodes spécifiques à un domaine (ex: `forgotPassword`, `welcomeEmail`). Elles injectent les variables (`setViewVars`) et définissent le template à utiliser.

## Convention d'Utilisation
Les mailers doivent être appelés de manière asynchrone ou au sein de blocs `try/catch` dans les contrôleurs pour ne pas paralyser le cycle de réponse HTTP en cas de défaillance du relais SMTP.

```php
use App\Mailer\UserMailer;

// ...

try {
// Instanciation et Envoi
$mailer = new UserMailer();
$mailer->send('forgotPassword', [$user]);
} catch (\Throwable $th) {
    $this->log("Échec de l'envoi SMTP pour {$email} : " . $th->getMessage(), 'error');
    // Selon la politique de sécurité, on peut choisir d'avertir ou non l'utilisateur
    // d'une panne SMTP, mais généralement on simule un succès pour ne pas fuiter
    // l'existence (ou non) de l'adresse email.
}

end;
```

