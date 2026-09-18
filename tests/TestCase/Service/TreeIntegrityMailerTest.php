<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Mailer\TreeIntegrityMailer;
use Cake\TestSuite\TestCase;

/** Tests unitaires de la composition des alertes d'intégrité TreeBehavior. */
class TreeIntegrityMailerTest extends TestCase
{
    public function testUneAlerteContientLeContexteEtLeDiagnostic(): void
    {
        $mailer = new TreeIntegrityMailer();
        $mailer->integrityAlert('infogestion@lemonde.fr', [[
            'table' => 'menus',
            'node_count' => 4,
            'issues' => [[
                'code' => 'CYCLE_PARENT',
                'message' => 'Cycle détecté dans la chaîne parent_id.',
                'details' => ['path' => [12, 19, 12]],
            ]],
        ]], 'gdaetf2-production', 'websrv-31', 'gdaetf-1', '2026-09-17T10:00:00+00:00', false);

        $this->assertArrayHasKey('infogestion@lemonde.fr', $mailer->getTo());
        $subject = iconv_mime_decode($mailer->getSubject(), ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
        $this->assertStringContainsString('gdaetf2-production', (string)$subject);
        $this->assertSame('tree_integrity_alert', $mailer->viewBuilder()->getTemplate());
    }
}
