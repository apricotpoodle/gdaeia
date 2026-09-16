<?php
declare(strict_types=1);

namespace App\Mailer;

use App\Model\Entity\Applicationform;
use App\Model\Entity\User;
use Cake\Core\Configure;

/** Courriels transactionnels du cycle de validation. */
final class ValidationWorkflowMailer extends AppMailer
{
    public function validationStep(User $recipient, Applicationform $applicationform): void
    {
        $this->setTo($recipient->email)->setSubject(__('Validation attendue — demande n°{0}', $applicationform->id))
            ->setViewVars(['recipient' => $recipient, 'applicationform' => $applicationform, 'url' => $this->url($applicationform)])
            ->viewBuilder()->setTemplate('validation_step');
    }

    public function finalResult(User $recipient, Applicationform $applicationform, string $state, ?string $comment): void
    {
        $label = $state === 'acceptee' ? __('acceptée') : __('refusée');
        $this->setTo($recipient->email)->setSubject(__('Demande n°{0} {1}', $applicationform->id, $label))
            ->setViewVars(['recipient' => $recipient, 'applicationform' => $applicationform, 'state' => $state, 'comment' => $comment, 'url' => $this->url($applicationform)])
            ->viewBuilder()->setTemplate('validation_final');
    }

    /**
     * Alerte les administrateurs lorsqu'une configuration incomplète empêche le lancement.
     *
     * @param list<string> $issues Préconditions de lancement non satisfaites.
     */
    public function validationBlocked(User $recipient, Applicationform $applicationform, array $issues): void
    {
        $this->setTo($recipient->email)
            ->setSubject(__('Configuration de validation à corriger — demande n°{0}', $applicationform->id))
            ->setViewVars([
                'recipient' => $recipient,
                'applicationform' => $applicationform,
                'issues' => $issues,
                'url' => $this->url($applicationform),
            ])
            ->viewBuilder()->setTemplate('validation_blocked');
    }

    private function url(Applicationform $applicationform): string
    {
        return rtrim((string)Configure::read('App.fullBaseUrl', 'http://localhost'), '/') . '/applicationforms/view/' . $applicationform->id . '?tab=validation';
    }
}
