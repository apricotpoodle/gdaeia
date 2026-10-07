<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Command\TreeIntegrityAlertTestCommand;
use Cake\Console\Arguments;

/** Vérifie les préconditions et le résultat de l’alerte TreeBehavior de test. */
class TreeIntegrityAlertTestCommandTest extends CommandTestCase
{
    public function testRefuseLenvoiSiLaConfigurationEstInvalide(): void
    {
        $sendCalled = false;
        $command = new TreeIntegrityAlertTestCommand(
            null,
            static fn(): array => ['Destinataire manquant.'],
            static function () use (&$sendCalled): bool {
                $sendCalled = true;

                return true;
            },
        );
        $streams = $this->createIo();

        $result = $command->execute(new Arguments([], [], []), $streams['io']);

        $this->assertSame(TreeIntegrityAlertTestCommand::CODE_ERROR, $result);
        $this->assertFalse($sendCalled);
        $this->assertStringContainsString('Destinataire manquant.', $this->streamContents($streams['err']));
    }

    public function testConfirmeUneAlerteEnvoyee(): void
    {
        $command = new TreeIntegrityAlertTestCommand(
            null,
            static fn(): array => [],
            static fn(): bool => true,
        );
        $streams = $this->createIo();

        $result = $command->execute(new Arguments([], [], []), $streams['io']);

        $this->assertSame(TreeIntegrityAlertTestCommand::CODE_SUCCESS, $result);
        $this->assertStringContainsString('Alerte de test envoyée', $this->streamContents($streams['out']));
    }

    public function testSignaleUnEchecDeRemise(): void
    {
        $command = new TreeIntegrityAlertTestCommand(
            null,
            static fn(): array => [],
            static fn(): bool => false,
        );
        $streams = $this->createIo();

        $result = $command->execute(new Arguments([], [], []), $streams['io']);

        $this->assertSame(TreeIntegrityAlertTestCommand::CODE_ERROR, $result);
        $this->assertStringContainsString('Alerte de test non remise', $this->streamContents($streams['err']));
    }
}
