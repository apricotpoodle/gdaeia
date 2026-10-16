<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Command\IntegrityAuditCommand;
use Cake\Console\Arguments;
use RuntimeException;

/** Vérifie le contrat CLI de l’audit global et son absence de mutation. */
final class IntegrityAuditCommandTest extends CommandTestCase
{
    public function testProduitUnRapportJsonPourLePerimetreDemande(): void
    {
        $scope = null;
        $command = new IntegrityAuditCommand(null, static function (string $requestedScope) use (&$scope): array {
            $scope = $requestedScope;

            return [
                'success' => true,
                'summary' => ['checks' => 1, 'rows_scanned' => 3, 'errors' => 0, 'warnings' => 0],
                'reports' => [['id' => 'references', 'status' => 'ok', 'rows_scanned' => 3, 'violations' => 0, 'issues' => []]],
            ];
        });
        $streams = $this->createIo();

        $result = $command->execute(
            new Arguments([], ['scope' => 'references', 'format' => 'json'], []),
            $streams['io'],
        );

        $this->assertSame(IntegrityAuditCommand::CODE_SUCCESS, $result);
        $this->assertSame('references', $scope);
        $this->assertSame('references', json_decode($streams['out']->contents, true, flags: JSON_THROW_ON_ERROR)['reports'][0]['id']);
    }

    public function testRetourneUnCodeAnomalieSansReparer(): void
    {
        $command = new IntegrityAuditCommand(null, static fn(string $scope): array => [
            'success' => false,
            'summary' => ['checks' => 1, 'rows_scanned' => 1, 'errors' => 1, 'warnings' => 0],
            'reports' => [['id' => 'workflow', 'status' => 'error', 'rows_scanned' => 1, 'violations' => 1, 'issues' => []]],
        ]);
        $streams = $this->createIo();

        $result = $command->execute(new Arguments([], [], []), $streams['io']);

        $this->assertSame(1, $result);
        $this->assertStringContainsString('workflow', $streams['err']->contents);
    }

    public function testRetourneUnCodeTechnique(): void
    {
        $command = new IntegrityAuditCommand(null, static fn(string $scope): array => throw new RuntimeException('Connexion indisponible.'));
        $streams = $this->createIo();

        $result = $command->execute(new Arguments([], ['format' => 'json'], []), $streams['io']);

        $this->assertSame(2, $result);
        $this->assertStringContainsString('Connexion indisponible.', $streams['out']->contents);
    }
}
