<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Command\ValidationReminderCommand;
use App\Model\Entity\Applicationform;
use App\Model\Entity\User;
use Cake\Console\Arguments;

/** Vérifie la collecte et l’expédition des relances de validation. */
class ValidationReminderCommandTest extends CommandTestCase
{
    public function testIndiqueLeNombreDeRelancesSansDestinataire(): void
    {
        $command = new ValidationReminderCommand(null, static fn(): array => []);
        $streams = $this->createIo();

        $result = $command->execute(new Arguments([], [], []), $streams['io']);

        $this->assertSame(ValidationReminderCommand::CODE_SUCCESS, $result);
        $this->assertStringContainsString('0 relance(s) envoyée(s).', $this->streamContents($streams['out']));
    }

    public function testEnvoieChaqueRelanceAChaqueDestinataire(): void
    {
        $applicationform = new Applicationform(['id' => 42]);
        $recipients = [
            new User(['id' => 1, 'email' => 'premier@example.test']),
            new User(['id' => 2, 'email' => 'second@example.test']),
        ];
        $sent = [];
        $command = new ValidationReminderCommand(
            null,
            static fn(): array => [[
                'applicationform' => $applicationform,
                'recipients' => $recipients,
            ]],
            static function (User $recipient, Applicationform $form) use (&$sent): bool {
                $sent[] = [$recipient->email, $form->id];

                return true;
            },
        );
        $streams = $this->createIo();

        $result = $command->execute(new Arguments([], [], []), $streams['io']);

        $this->assertSame(ValidationReminderCommand::CODE_SUCCESS, $result);
        $this->assertSame([
            ['premier@example.test', 42],
            ['second@example.test', 42],
        ], $sent);
        $this->assertStringContainsString('1 relance(s) envoyée(s).', $this->streamContents($streams['out']));
    }
}
