<?php
declare(strict_types=1);

namespace App\Mailer;

use App\Model\Entity\Applicationform;
use App\Model\Entity\User;
use Cake\Core\Configure;

/** Courriels transactionnels du cycle de validation. */
final class ValidationWorkflowMailer extends AppMailer
{
    /**
     * Prépare le récapitulatif quotidien des cycles bloqués.
     *
     * @param list<\App\Service\Workflow\BlockedValidationCycle> $blockedCycles Cycles bloqués.
     */
    public function blockedValidationDigest(User $recipient, array $blockedCycles): void
    {
        $this->setTo($recipient->get(User::FIELD_EMAIL))
            ->setSubject(__('Cycles de validation bloqués — {0} DAE', count($blockedCycles)))
            ->setViewVars([
                'recipient' => $recipient,
                'blockedCycles' => $blockedCycles,
                'count' => count($blockedCycles),
                'baseUrl' => rtrim((string)Configure::read('App.fullBaseUrl', 'http://localhost'), '/'),
            ])
            ->viewBuilder()->setTemplate('blocked_validation_digest');
    }

    /**
     * Prépare la synthèse quotidienne des demandes en attente.
     *
     * @param list<\App\Model\Entity\Applicationform> $applicationforms Demandes.
     */
    public function pendingValidationDigest(User $recipient, array $applicationforms): void
    {
        $this->setTo($recipient->get(User::FIELD_EMAIL))
            ->setSubject(__('Demandes à valider — {0} DAE', count($applicationforms)))
            ->setViewVars([
                'recipient' => $recipient,
                'applicationforms' => $applicationforms,
                'baseUrl' => rtrim((string)Configure::read('App.fullBaseUrl', 'http://localhost'), '/'),
            ])
            ->viewBuilder()->setTemplate('pending_validation_digest');
    }

    /**
     * Prépare la synthèse hebdomadaire des demandes entièrement validées.
     *
     * @param list<\App\Model\Entity\Applicationform> $applicationforms Demandes.
     */
    public function acceptedValidationDigest(User $recipient, array $applicationforms): void
    {
        $this->setTo($recipient->get(User::FIELD_EMAIL))
            ->setSubject(__('Demandes validées cette semaine — {0} DAE', count($applicationforms)))
            ->setViewVars(['recipient' => $recipient, 'applicationforms' => $applicationforms])
            ->viewBuilder()->setTemplate('accepted_validation_digest');
    }

    /**
     * Prépare le courriel de notification ou de relance d'une étape de validation.
     *
     * @param \App\Model\Entity\User $recipient Destinataire.
     * @param \App\Model\Entity\Applicationform $applicationform Demande concernée.
     */
    public function validationStep(User $recipient, Applicationform $applicationform): void
    {
        $this->prepareValidationStep(
            $recipient,
            $applicationform,
            __('Validation attendue — demande n°{0}', $applicationform->get(Applicationform::FIELD_ID)),
        );
    }

    /** Prépare une relance adressée à un validateur concerné par une étape bloquée. */
    public function validationReminder(User $recipient, Applicationform $applicationform): void
    {
        $this->prepareValidationStep(
            $recipient,
            $applicationform,
            __('Relance de validation — demande n°{0}', $applicationform->get(Applicationform::FIELD_ID)),
        );
    }

    /**
     * Prépare le courriel annonçant le résultat final du cycle.
     *
     * @param \App\Model\Entity\User $recipient Destinataire.
     * @param \App\Model\Entity\Applicationform $applicationform Demande concernée.
     * @param string $state État final.
     * @param string|null $comment Commentaire associé.
     */
    public function finalResult(
        User $recipient,
        Applicationform $applicationform,
        string $state,
        ?string $comment,
    ): void {
        $label = $state === 'acceptee' ? __('acceptée') : __('refusée');
        $this->setTo($recipient->get(User::FIELD_EMAIL))
            ->setSubject(__('Demande n°{0} {1}', $applicationform->get(Applicationform::FIELD_ID), $label))
            ->setViewVars([
                'recipient' => $recipient,
                'applicationform' => $applicationform,
                'state' => $state,
                'comment' => $comment,
                'url' => $this->url($applicationform),
            ])
            ->viewBuilder()->setTemplate('validation_final');
    }

    /**
     * Alerte les administrateurs lorsqu'une configuration incomplète empêche le lancement.
     *
     * @param list<string> $issues Préconditions de lancement non satisfaites.
     */
    public function validationBlocked(User $recipient, Applicationform $applicationform, array $issues): void
    {
        $this->setTo($recipient->get(User::FIELD_EMAIL))
            ->setSubject(__('Configuration de validation à corriger — demande n°{0}', $applicationform->id))
            ->setViewVars([
                'recipient' => $recipient,
                'applicationform' => $applicationform,
                'issues' => $issues,
                'url' => $this->url($applicationform),
            ])
            ->viewBuilder()->setTemplate('validation_blocked');
    }

    /**
     * Construit l'URL de la page de validation d'une demande.
     *
     * @param \App\Model\Entity\Applicationform $applicationform Demande concernée.
     * @return string URL de la demande.
     */
    private function url(Applicationform $applicationform): string
    {
        return rtrim((string)Configure::read('App.fullBaseUrl', 'http://localhost'), '/')
            . '/applicationforms/view/' . $applicationform->id . '?tab=validation';
    }

    /** Configure le courriel commun aux notifications et relances de validation. */
    private function prepareValidationStep(User $recipient, Applicationform $applicationform, string $subject): void
    {
        $this->setTo($recipient->get(User::FIELD_EMAIL))
            ->setSubject($subject)
            ->setViewVars([
                'recipient' => $recipient,
                'applicationform' => $applicationform,
                'url' => $this->url($applicationform),
            ])
            ->viewBuilder()->setTemplate('validation_step');
    }
}
