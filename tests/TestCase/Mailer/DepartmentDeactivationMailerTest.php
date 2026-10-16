<?php
declare(strict_types=1);

namespace App\Test\TestCase\Mailer;

use App\Mailer\DepartmentDeactivationMailer;
use App\Model\Entity\User;
use Cake\TestSuite\TestCase;

/** Vérifie la composition des comptes rendus de désactivation. */
final class DepartmentDeactivationMailerTest extends TestCase
{
    /** Vérifie qu'un compte rendu complet expose les opérations effectuées. */
    public function testLeCompteRenduCompletExposeLesOperations(): void
    {
        $mailer = new DepartmentDeactivationMailer();

        $mailer->report(
            new User(['email' => 'admin@example.test']),
            [
                'department_ids' => [2],
                'detached_user_ids' => [7],
                'deactivated_sequence_ids' => [3],
                'deactivated_cgr_ids' => [4],
                'cleared_default_department_ids' => [],
            ],
            true,
        );

        $this->assertArrayHasKey('admin@example.test', $mailer->getTo());
        $subject = iconv_mime_decode($mailer->getSubject(), ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
        $this->assertStringContainsString('désactivation', (string)$subject);
        $this->assertSame('department_deactivation', $mailer->viewBuilder()->getTemplate());
    }
}
