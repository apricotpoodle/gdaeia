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
        );
        $streams = $this->createIo();

        $result = $command->execute(new Arguments([], [], []), $streams['io']);

        $this->assertSame(BlockedValidationDigestCommand::CODE_ERROR, $result);
        $this->assertStringContainsString('Échec', $this->streamContents($streams['err']));
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
