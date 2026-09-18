<?php
declare(strict_types=1);

namespace App\Test\TestCase\Mailer;

use App\Mailer\ValidationWorkflowMailer;
use App\Model\Entity\Applicationform;
use App\Model\Entity\User;
use Cake\TestSuite\TestCase;

/** Vérifie la composition des courriels de lancement du workflow. */
class ValidationWorkflowMailerTest extends TestCase
{
    /** Vérifie que le validateur reçoit un courriel portant vers la demande à traiter. */
    public function testLeCourrielDeValidationCibleLeValidateurEtLeBonTemplate(): void
    {
        $recipient = new User(['email' => 'validateur@example.test']);
        $applicationform = new Applicationform(['id' => 42]);
        $mailer = new ValidationWorkflowMailer();

        $mailer->validationStep($recipient, $applicationform);

        $this->assertArrayHasKey('validateur@example.test', $mailer->getTo());
        $subject = iconv_mime_decode($mailer->getSubject(), ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
        $this->assertStringContainsString('demande n°42', (string)$subject);
        $this->assertSame('validation_step', $mailer->viewBuilder()->getTemplate());
    }

    /** Vérifie la composition du courriel de clôture par acceptation. */
    public function testLeCourrielFinalDAcceptationCibleLeDemandeur(): void
    {
        $mailer = new ValidationWorkflowMailer();
        $mailer->finalResult(
            new User(['email' => 'demandeur@example.test']),
            new Applicationform(['id' => 42]),
            'acceptee',
            'Accord final.',
        );

        $this->assertArrayHasKey('demandeur@example.test', $mailer->getTo());
        $this->assertSame('validation_final', $mailer->viewBuilder()->getTemplate());
        $subject = iconv_mime_decode($mailer->getSubject(), ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
        $this->assertStringContainsString('acceptée', (string)$subject);
    }

    /** Vérifie la composition du courriel de clôture par refus. */
    public function testLeCourrielFinalDeRefusCibleLeDemandeur(): void
    {
        $mailer = new ValidationWorkflowMailer();
        $mailer->finalResult(
            new User(['email' => 'demandeur@example.test']),
            new Applicationform(['id' => 42]),
            'refusee',
            'Budget insuffisant.',
        );

        $this->assertArrayHasKey('demandeur@example.test', $mailer->getTo());
        $this->assertSame('validation_final', $mailer->viewBuilder()->getTemplate());
        $subject = iconv_mime_decode($mailer->getSubject(), ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
        $this->assertStringContainsString('refusée', (string)$subject);
    }
}
