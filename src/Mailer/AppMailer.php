<?php

declare(strict_types=1);

namespace App\Mailer;

use Cake\Mailer\Mailer;
use Cake\Core\Configure;
use App\Log\EmailLoggerTrait;

/**
 * @class AppMailer
 * @description Socle d'infrastructure commun pour l'ensemble des classes Mailer de l'application.
 * Centralise les configurations transverses et encapsule l'expédition sécurisée (try/catch + logs).
 * @package App\Mailer
 */
class AppMailer extends Mailer
{
    // Injection de notre outil de journalisation percutant
    use EmailLoggerTrait;

    /**
     * Initialisation globale pour tous les courriels sortants.
     *
     * @param array<string, mixed>|string|null $config Configuration du mailer.
     */
    public function __construct(array|string|null $config = null)
    {
        parent::__construct($config);

        $defaultFrom = Configure::read('Email.default.from');

        if ($defaultFrom) {
            $this->setFrom($defaultFrom);
        }

        $this->setEmailFormat('both');
    }

    /**
     * Exécute l'expédition du courriel en interceptant les pannes SMTP
     * et en automatisant la journalisation dans logs/email.log.
     *
     * @param string $action Le nom de la méthode à appeler dans le Mailer enfant.
     * @param array $args Les arguments à passer à cette méthode (ex: [$user]).
     * @return bool True si l'envoi a réussi, False si le serveur SMTP a échoué.
     */
    public function safeSend(string $action, array $args = []): bool
    {
        try {
            // Appel à la méthode send() native de CakePHP
            $this->send($action, $args);

            // Extraction des destinataires pour avoir un log lisible
            $to = implode(', ', array_keys($this->getTo()));
            $this->traceEmail("Succès SMTP : Courriel '{$action}' expédié vers {$to}.");

            return true;
        } catch (\Throwable $th) {
            // Si l'erreur survient avant l'assignation du destinataire, on évite un crash
            $toArray = $this->getTo();
            $to = !empty($toArray) ? implode(', ', array_keys($toArray)) : 'destinataire non défini';

            $this->traceEmail("Échec SMTP pour l'action '{$action}' vers {$to} : " . $th->getMessage(), 'error');

            return false; // Étouffe l'exception pour ne pas faire crasher l'application
        }
    }
}
