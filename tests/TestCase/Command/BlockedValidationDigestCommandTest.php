<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Command\BlockedValidationDigestCommand;
use App\Model\Entity\Applicationform;
use App\Model\Entity\Applicationvalidationstep;
use App\Model\Entity\Department;
use App\Model\Entity\Role;
use App\Model\Entity\User;
use App\Service\Workflow\BlockedValidationCycle;
use App\Service\Workflow\BlockedValidationStep;
use Cake\Console\Arguments;
use Cake\I18n\DateTime;
use Closure;

/** Vérifie l'orchestration du récapitulatif quotidien des cycles bloqués. */
class BlockedValidationDigestCommandTest extends CommandTestCase
{
    public function testNEnvoieRienSansDaeBloquee(): void
    {
        $sent = 0;
        $command = new BlockedValidationDigestCommand(
            null,
            static fn(): array => [new User(['email' => 'admin@example.test'])],
            static fn(User $user): array => [],
            static function () use (&$sent): bool {
                $sent++;

                return true;
            },
        );
        $streams = $this->createIo();

        $result = $command->execute(new Arguments([], [], []), $streams['io']);

        $this->assertSame(BlockedValidationDigestCommand::CODE_SUCCESS, $result);
        $this->assertSame(0, $sent);
        $this->assertStringContainsString('0 destinataire(s)', $this->streamContents($streams['out']));
    }

    public function testEnvoieUnSeulCourrielParAdministrateur(): void
    {
        $cycle = $this->createBlockedCycle();
        $sent = [];
        $command = new BlockedValidationDigestCommand(
            null,
            static fn(): array => [new User(['id' => 1, 'email' => 'admin@example.test'])],
            fn(User $user): array => [$cycle],
            static function (User $user, array $cycles) use (&$sent): bool {
                $sent[] = [$user->email, count($cycles)];

                return true;
            },
            static fn(string $type, string $eventKey, User $recipient, Closure $send): bool => $send(),
        );
        $streams = $this->createIo();

        $result = $command->execute(new Arguments([], [], []), $streams['io']);

        $this->assertSame(BlockedValidationDigestCommand::CODE_SUCCESS, $result);
        $this->assertSame([['admin@example.test', 1]], $sent);
    }

    public function testRetourneUnEchecSiUnEnvoiEchoue(): void
    {
        $cycle = $this->createBlockedCycle();
        $command = new BlockedValidationDigestCommand(
            null,
            static fn(): array => [new User(['email' => 'admin@example.test'])],
            fn(User $user): array => [$cycle],
            static fn(): bool => false,
            static fn(string $type, string $eventKey, User $recipient, Closure $send): bool => $send(),
        );
        $streams = $this->createIo();

        $result = $command->execute(new Arguments([], [], []), $streams['io']);

        $this->assertSame(BlockedValidationDigestCommand::CODE_ERROR, $result);
        $this->assertStringContainsString('Échec', $this->streamContents($streams['err']));
    }

    public function testContinueApresUnEchecEtNeRenvoiePasLeSuccesConfirme(): void
    {
        $cycle = $this->createBlockedCycle();
        $first = new User(['id' => 1, 'email' => 'premier@example.test']);
        $second = new User(['id' => 2, 'email' => 'second@example.test']);
        $attempts = [];
        $confirmed = [];
        $send = static function (User $user) use (&$attempts): bool {
            $email = strtolower($user->email);
            $attempts[$email] = ($attempts[$email] ?? 0) + 1;

            return $email !== 'second@example.test' || $attempts[$email] > 1;
        };
        $deliveries = [];
        $sendOnce = static function (string $type, string $eventKey, User $recipient, Closure $callback)
 use (&$confirmed, &$deliveries): bool {
                $deliveries[] = [$type, $eventKey];
                $key = $type . ':' . $eventKey . ':' . strtolower(trim($recipient->email));
            if (isset($confirmed[$key])) {
                return true;
            }
            if (!$callback()) {
                return false;
            }
                $confirmed[$key] = true;

                return true;
        };
        $command = new BlockedValidationDigestCommand(
            null,
            static fn(): array => [$first, $second],
            static fn(User $user): array => [$cycle],
            static fn(User $user, array $cycles): bool => $send($user),
            $sendOnce,
        );
        $firstStreams = $this->createIo();

        $firstResult = $command->execute(new Arguments([], [], []), $firstStreams['io']);
        $this->assertSame(BlockedValidationDigestCommand::CODE_ERROR, $firstResult);

        $secondStreams = $this->createIo();
        $secondResult = $command->execute(new Arguments([], [], []), $secondStreams['io']);

        $this->assertSame(BlockedValidationDigestCommand::CODE_SUCCESS, $secondResult);
        $this->assertSame([
            'premier@example.test' => 1,
            'second@example.test' => 2,
        ], $attempts);
        $this->assertSame(['blocked_digest'], array_values(array_unique(array_column($deliveries, 0))));
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $deliveries[0][1]);
        $this->assertStringNotContainsString('example.test', $this->streamContents($secondStreams['out']));
    }

    /** Construit un cycle minimal conforme à l'invariant des étapes bloquées. */
    private function createBlockedCycle(): BlockedValidationCycle
    {
        $activatedAt = new DateTime('2026-10-01 09:00:00');
        $step = new BlockedValidationStep(
            new Applicationvalidationstep(['id' => 7]),
            new Role(['name' => 'Direction']),
            $activatedAt,
            new DateTime('2026-10-06 09:00:00'),
            5,
        );

        return new BlockedValidationCycle(
            new Applicationform(['id' => 42]),
            42,
            null,
            new Department(['name' => 'Ressources humaines']),
            [$step],
            '/applicationforms/view/42?tab=validation',
        );
    }
}
