<?php
declare(strict_types=1);

namespace App\Mailer;

use App\Model\Entity\User;
use Cake\Core\Configure;

/** Compose les comptes rendus de désactivation de départements. */
final class DepartmentDeactivationMailer extends AppMailer
{
    /**
     * Prépare un compte rendu de désactivation.
     *
     * @param \App\Model\Entity\User $recipient Destinataire utilisateur.
     * @param array<string, mixed> $report Rapport de nettoyage.
     * @param bool $fullReport Autorise le détail des identifiants traités.
     */
    public function report(User $recipient, array $report, bool $fullReport): void
    {
        $this->setTo($recipient->get(User::FIELD_EMAIL))
            ->setSubject(__('Compte rendu de désactivation de département'))
            ->setViewVars([
                'recipient' => $recipient,
                'report' => $report,
                'fullReport' => $fullReport,
                'baseUrl' => rtrim((string)Configure::read('App.fullBaseUrl', 'http://localhost'), '/'),
            ])
            ->viewBuilder()->setTemplate('department_deactivation');
    }

    /**
     * Prépare une copie de compte rendu destinée à une adresse d’audit.
     *
     * @param array<string, mixed> $report Rapport de nettoyage.
     */
    public function auditReport(string $recipient, array $report): void
    {
        $this->setTo($recipient)
            ->setSubject(__('Audit — désactivation de département'))
            ->setViewVars([
                'report' => $report,
                'fullReport' => true,
                'baseUrl' => rtrim((string)Configure::read('App.fullBaseUrl', 'http://localhost'), '/'),
            ])
            ->viewBuilder()->setTemplate('department_deactivation');
    }
}
