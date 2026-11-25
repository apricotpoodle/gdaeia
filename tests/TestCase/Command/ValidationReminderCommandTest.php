<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Command\ValidationReminderCommand;
use App\Model\Entity\Applicationform;
use App\Model\Entity\User;
use Cake\Console\Arguments;
use Closure;

/** Vérifie la collecte et l’expédition des relances de validation. */
class ValidationReminderCommandTest extends CommandTestCase
{
    public function testIndiqueLeNombreDeRelancesSansDestinataire(): void
    {
        $command = new ValidationReminderCommand(null, static fn(): array => []);
        $streams = $this->createIo();

        $result = $command->execute(new Arguments([], [], []), $streams['io']);

        $this->assertSame(ValidationReminderCommand::CODE_SUCCESS, $result);
        $this->assertStringContainsString('0 réussite(s), 0 échec(s)', $this->streamContents($streams['out']));
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
                'step_id' => 7,
                'reminder_count' => 0,
                'applicationform' => $applicationform,
                'recipients' => $recipients,
            ]],
            static function (User $recipient, Applicationform $form) use (&$sent): bool {
                $sent[] = [$recipient->email, $form->id];

                return true;
            },
            static fn(int $stepId, int $reminderCount): bool => $stepId === 7 && $reminderCount === 1,
            static fn(string $type, string $eventKey, User $recipient, Closure $send): bool => $send(),
        );
        $streams = $this->createIo();

        $result = $command->execute(new Arguments([], [], []), $streams['io']);

        $this->assertSame(ValidationReminderCommand::CODE_SUCCESS, $result);
        $this->assertSame([
            ['premier@example.test', 42],
            ['second@example.test', 42],
        ], $sent);
        $this->assertStringContainsString('2 réussite(s), 0 échec(s)', $this->streamContents($streams['out']));
    }

    public function testNeRenvoieQueLeDestinataireEnEchecSansAvancerLEtapeAvantSucces(): void
    {
        $applicationform = new Applicationform(['id' => 42]);
        $recipients = [
            new User(['id' => 1, 'email' => 'premier@example.test']),
            new User(['id' => 2, 'email' => 'second@example.test']),
        ];
        $record = [
            'step_id' => 7,
            'reminder_count' => 2,
            'applicationform' => $applicationform,
            'recipients' => $recipients,
        ];
        $attempts = [];
        $confirmed = [];
        $completed = [];
        $collect = static fn(): array => [$record];
        $send = static function (User $recipient) use (&$attempts): bool {
            $email = strtolower($recipient->email);
            $attempts[$email] = ($attempts[$email] ?? 0) + 1;

            return $email !== 'second@example.test' || $attempts[$email] > 1;
        };
        $sendOnce = static function (string $type, string $eventKey, User $recipient, Closure $send)
 use (&$confirmed): bool {
                $key = $type . ':' . $eventKey . ':' . strtolower(trim($recipient->email));
            if (isset($confirmed[$key])) {
                return true;
            }
            if (!$send()) {
                return false;
            }
                $confirmed[$key] = true;

                return true;
        };
        $complete = static function (int $stepId, int $reminderCount) use (&$completed): bool {
            $completed[] = [$stepId, $reminderCount];

            return true;
        };
        $command = new ValidationReminderCommand(null, $collect, $send, $complete, $sendOnce);
        $firstStreams = $this->createIo();

        $firstResult = $command->execute(new Arguments([], [], []), $firstStreams['io']);
        $this->assertSame(ValidationReminderCommand::CODE_ERROR, $firstResult);
        $this->assertSame([], $completed);

        $secondStreams = $this->createIo();
        $secondResult = $command->execute(new Arguments([], [], []), $secondStreams['io']);

        $this->assertSame(ValidationReminderCommand::CODE_SUCCESS, $secondResult);
        $this->assertSame([
            'premier@example.test' => 1,
            'second@example.test' => 2,
        ], $attempts);
        $this->assertSame([[7, 3]], $completed);
    }

    public function testSansDestinataireNeConstruitPasDeCourrielEtNAvancePasLEtape(): void
    {
        $sent = 0;
        $completed = 0;
        $command = new ValidationReminderCommand(
            null,
            static fn(): array => [[
                'step_id' => 7,
                'reminder_count' => 2,
                'applicationform' => new Applicationform(['id' => 42]),
                'recipients' => [],
            ]],
            static function () use (&$sent): bool {
                $sent++;

                return true;
            },
            static function () use (&$completed): void {
                $completed++;
            },
            static fn(string $type, string $eventKey, User $recipient, Closure $send): bool => $send(),
        );
        $streams = $this->createIo();

        $result = $command->execute(new Arguments([], [], []), $streams['io']);

        $this->assertSame(ValidationReminderCommand::CODE_SUCCESS, $result);
        $this->assertSame(0, $sent);
        $this->assertSame(0, $completed);
    }
}
