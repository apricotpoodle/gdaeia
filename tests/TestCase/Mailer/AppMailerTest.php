<?php
declare(strict_types=1);

namespace App\Test\TestCase\Mailer;

use App\Mailer\ValidationWorkflowMailer;
use App\Model\Entity\Applicationform;
use App\Model\Entity\User;
use Cake\Log\Engine\ArrayLog;
use Cake\Log\Log;
use Cake\Mailer\AbstractTransport;
use Cake\Mailer\Message;
use Cake\TestSuite\TestCase;
use RuntimeException;

/** Vérifie la journalisation des succès et échecs d'expédition. */
class AppMailerTest extends TestCase
{
    private static ?ArrayLog $sharedEmailLogger = null;

    private ArrayLog $emailLogger;

    protected function setUp(): void
    {
        parent::setUp();
        self::$sharedEmailLogger ??= new ArrayLog([
            'levels' => [],
            'scopes' => ['email'],
        ]);
        if (Log::engine('app_mailer_test') === null) {
            Log::setConfig('app_mailer_test', self::$sharedEmailLogger);
        }
        self::$sharedEmailLogger->clear();
        $this->emailLogger = self::$sharedEmailLogger;
    }

    public function testUnSuccesSmtpEstJournaliseAvecLeDestinataire(): void
    {
        $mailer = new ValidationWorkflowMailer();
        $mailer->setTransport(new class extends AbstractTransport {
            /** @param \Cake\Mailer\Message $message */
            public function send(Message $message): array
            {
                return ['headers' => '', 'message' => ''];
            }
        });

        $sent = $mailer->safeSend('validationStep', [
            new User(['email' => 'validateur@example.test', 'firstname' => 'Valérie']),
            new Applicationform(['id' => 42]),
        ]);

        $this->assertTrue($sent);
        $this->assertLogContains('Succès SMTP');
        $this->assertLogContains('validateur@example.test');
        $this->assertLogContains('Validation attendue');
    }

    public function testUnEchecSmtpEstJournaliseAvecLeDestinataireEtRetourneFaux(): void
    {
        $mailer = new ValidationWorkflowMailer();
        $mailer->setTransport(new class extends AbstractTransport {
            /** @param \Cake\Mailer\Message $message */
            public function send(Message $message): array
            {
                throw new RuntimeException('Relais SMTP indisponible.');
            }
        });

        $sent = $mailer->safeSend('validationStep', [
            new User(['email' => 'validateur@example.test', 'firstname' => 'Valérie']),
            new Applicationform(['id' => 42]),
        ]);

        $this->assertFalse($sent);
        $this->assertLogContains('Échec SMTP');
        $this->assertLogContains('validateur@example.test');
        $this->assertLogContains('Relais SMTP indisponible.');
    }

    private function assertLogContains(string $expected): void
    {
        $this->assertTrue(
            count(array_filter(
                $this->emailLogger->read(),
                static fn(string $message): bool => str_contains($message, $expected),
            )) > 0,
            sprintf('Le journal email ne contient pas « %s ».', $expected),
        );
    }
}
