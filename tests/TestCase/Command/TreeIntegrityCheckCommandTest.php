<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Command\TreeIntegrityCheckCommand;
use Cake\Console\Arguments;
use InvalidArgumentException;

/** Vérifie les formats et les codes de sortie du diagnostic d’intégrité. */
class TreeIntegrityCheckCommandTest extends CommandTestCase
{
    public function testVerifieLaConfigurationSansInterrogerLesArbres(): void
    {
        $checkCalled = false;
        $command = new TreeIntegrityCheckCommand(
            null,
            static function () use (&$checkCalled): array {
                $checkCalled = true;

                return [];
            },
            static fn(): array => [],
        );
        $streams = $this->createIo();

        $result = $command->execute(
            new Arguments([], ['check-config' => true], []),
            $streams['io'],
        );

        $this->assertSame(TreeIntegrityCheckCommand::CODE_SUCCESS, $result);
        $this->assertFalse($checkCalled);
        $this->assertStringContainsString('Configuration des alertes TreeBehavior valide', $this->streamContents($streams['out']));
    }

    public function testRetourneUneErreurDeConfiguration(): void
    {
        $command = new TreeIntegrityCheckCommand(
            null,
            static fn(): array => [],
            static fn(): array => ['APP_INSTANCE_NAME est obligatoire.'],
        );
        $streams = $this->createIo();

        $result = $command->execute(
            new Arguments([], ['check-config' => true], []),
            $streams['io'],
        );

        $this->assertSame(TreeIntegrityCheckCommand::CODE_ERROR, $result);
        $this->assertStringContainsString('APP_INSTANCE_NAME est obligatoire.', $this->streamContents($streams['err']));
    }

    public function testTransformeUneTableInvalideEnErreurCli(): void
    {
        $command = new TreeIntegrityCheckCommand(
            null,
            static fn(?string $table): array => throw new InvalidArgumentException('Table inconnue.'),
        );
        $streams = $this->createIo();

        $result = $command->execute(
            new Arguments([], ['table' => 'inconnue', 'format' => 'text'], []),
            $streams['io'],
        );

        $this->assertSame(TreeIntegrityCheckCommand::CODE_ERROR, $result);
        $this->assertStringContainsString('Table inconnue.', $this->streamContents($streams['err']));
    }

    public function testRetourneUnRapportJsonEnCasDeSucces(): void
    {
        $reports = [[
            'table' => 'menus',
            'node_count' => 2,
            'success' => true,
            'issues' => [],
            'check_command' => 'tree integrity check --table menus',
        ]];
        $command = new TreeIntegrityCheckCommand(null, static fn(?string $table): array => $reports);
        $streams = $this->createIo();

        $result = $command->execute(
            new Arguments([], ['format' => 'json'], []),
            $streams['io'],
        );

        $this->assertSame(TreeIntegrityCheckCommand::CODE_SUCCESS, $result);
        $payload = json_decode($this->streamContents($streams['out']), true, flags: JSON_THROW_ON_ERROR);
        $this->assertTrue($payload['success']);
        $this->assertSame('menus', $payload['reports'][0]['table']);
    }

    public function testSignaleLesIncoherencesEtAlerteEnFormatTexte(): void
    {
        $sentReports = null;
        $reports = [[
            'table' => 'departments',
            'node_count' => 3,
            'success' => false,
            'issues' => [[
                'code' => 'PARENT_INEXISTANT',
                'message' => 'Le parent référencé n’existe pas.',
                'details' => ['missing_parent_id' => 99],
            ]],
            'check_command' => 'tree integrity check --table departments',
        ]];
        $command = new TreeIntegrityCheckCommand(
            null,
            static fn(?string $table): array => $reports,
            static fn(): array => [],
            static function (array $failedReports) use (&$sentReports): bool {
                $sentReports = $failedReports;

                return true;
            },
        );
        $streams = $this->createIo();

        $result = $command->execute(
            new Arguments([], ['format' => 'text'], []),
            $streams['io'],
        );

        $output = $this->streamContents($streams['out']);
        $this->assertSame(TreeIntegrityCheckCommand::CODE_ERROR, $result);
        $this->assertSame($reports, $sentReports);
        $this->assertStringContainsString('PARENT_INEXISTANT', $output);
        $this->assertStringContainsString(
            'Alerte d’intégrité envoyée',
            $this->streamContents($streams['err']),
        );
        $this->assertStringContainsString('Diagnostic ciblé', $output);
    }
}
