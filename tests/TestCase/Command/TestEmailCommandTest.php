<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Command\TestEmailCommand;
use App\Model\Entity\User;
use Cake\Console\Arguments;

/** Vérifie le résultat CLI de la commande d’envoi de courriel de test. */
class TestEmailCommandTest extends CommandTestCase
{
    public function testEnvoieLeCourrielAvecUneEntiteUtilisateurDeTest(): void
    {
        $sentUser = null;
        $command = new TestEmailCommand(
            null,
            static fn(string $email): User => new User([
                'email' => $email,
                'token' => 'TEST-TOKEN-123456789',
            ]),
            static function (User $user) use (&$sentUser): bool {
                $sentUser = $user;

                return true;
            },
        );
        $streams = $this->createIo();

        $result = $command->execute(
            new Arguments(['destinataire@example.test'], [], ['email']),
            $streams['io'],
        );

        $this->assertSame(TestEmailCommand::CODE_SUCCESS, $result);
        $this->assertInstanceOf(User::class, $sentUser);
        $this->assertSame('destinataire@example.test', $sentUser->email);
        $this->assertSame('TEST-TOKEN-123456789', $sentUser->token);
        $this->assertStringContainsString('Succès', $this->streamContents($streams['out']));
    }

    public function testRetourneUneErreurSiLeRelaisRefuseLeCourriel(): void
    {
        $command = new TestEmailCommand(
            null,
            static fn(string $email): User => new User(['email' => $email]),
            static fn(User $user): bool => false,
        );
        $streams = $this->createIo();

        $result = $command->execute(
            new Arguments(['destinataire@example.test'], [], ['email']),
            $streams['io'],
        );

        $this->assertSame(TestEmailCommand::CODE_ERROR, $result);
        $this->assertStringContainsString('Échec', $this->streamContents($streams['err']));
    }
}
