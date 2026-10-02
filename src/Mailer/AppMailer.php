<?php
declare(strict_types=1);

namespace App\Mailer;

use App\Log\EmailLoggerTrait;
use Cake\Core\Configure;
use Cake\Mailer\Mailer;
use Throwable;

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

    private ?string $safeSendAction = null;

    /**
     * Contexte conservé pendant l'envoi avant la restauration CakePHP.
     *
     * @var array{action: string, recipients: string, subject: string}|null
     */
    private ?array $deliveryContext = null;

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
     * @param list<mixed> $args Les arguments à passer à cette méthode (ex: [$user]).
     * @return bool True si l'envoi a réussi, False si le serveur SMTP a échoué.
     */
    public function safeSend(string $action, array $args = []): bool
    {
        $this->safeSendAction = $action;
        $this->deliveryContext = null;
        try {
            // Appel à la méthode send() native de CakePHP
            $this->send($action, $args);
            $context = $this->deliveryContext ?? $this->logContext($action);
            $this->traceEmail($this->formatLogMessage('Succès SMTP', ...$context));

            return true;
        } catch (Throwable $th) {
            $context = $this->deliveryContext ?? $this->logContext($action);
            $this->traceFailure($th, ...$context);

            return false; // Étouffe l'exception pour ne pas faire crasher l'application
        } finally {
            $this->safeSendAction = null;
            $this->deliveryContext = null;
        }
    }

    /**
     * Expédie le message et journalise le résultat avec ses destinataires réels.
     *
     * CakePHP restaure l'état du message après l'appel à send(). La journalisation
     * doit donc être effectuée ici, avant cette restauration, faute de quoi les
     * destinataires apparaissent vides dans le journal.
     *
     * @param string $content Corps déjà rendu, le cas échéant.
     * @return array<string, mixed> Résultat de l'expédition SMTP.
     * @phpstan-return array{headers: string, message: string, ...}
     * @throws \Throwable Si le transport SMTP refuse l'expédition.
     */
    public function deliver(string $content = ''): array
    {
        $action = $this->safeSendAction ?? 'expédition directe';
        $recipients = $this->formatRecipients();
        $subject = $this->getSubject();
        $this->deliveryContext = [
            'action' => $action,
            'recipients' => $recipients,
            'subject' => $subject,
        ];

        try {
            $result = parent::deliver($content);
            if ($this->safeSendAction === null) {
                $this->traceEmail($this->formatLogMessage('Succès SMTP', $action, $recipients, $subject));
            }

            return $result;
        } catch (Throwable $th) {
            if ($this->safeSendAction === null) {
                $this->traceFailure($th, $action, $recipients, $subject);
            }
            throw $th;
        }
    }

    /** Journalise une erreur d'expédition avec le contexte disponible. */
    private function traceFailure(
        Throwable $exception,
        string $action,
        string $recipients,
        string $subject,
    ): void {
        $this->traceEmail(
            $this->formatLogMessage('Échec SMTP', $action, $recipients, $subject)
            . ': ' . $exception->getMessage(),
            'error',
        );
    }

    /** @return array{action: string, recipients: string, subject: string} */
    private function logContext(string $action): array
    {
        return [
            'action' => $action,
            'recipients' => $this->formatRecipients(),
            'subject' => $this->getSubject(),
        ];
    }

    /** Retourne la liste des destinataires configurés pour le message courant. */
    private function formatRecipients(): string
    {
        $recipients = array_keys($this->getTo());

        return $recipients === [] ? 'destinataire non défini' : implode(', ', $recipients);
    }

    /** Construit un message de log homogène pour les succès et les échecs. */
    private function formatLogMessage(
        string $status,
        string $action,
        string $recipients,
        string $subject,
    ): string {
        return sprintf(
            "%s : action '%s', destinataire(s) %s, sujet '%s'",
            $status,
            $action,
            $recipients,
            $subject,
        );
    }
}
