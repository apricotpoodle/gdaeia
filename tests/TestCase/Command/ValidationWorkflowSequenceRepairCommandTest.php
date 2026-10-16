<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Command\ValidationWorkflowSequenceRepairCommand;
use Cake\Console\Arguments;

/** Vérifie le diagnostic et l’application de la réparation des séquences. */
class ValidationWorkflowSequenceRepairCommandTest extends CommandTestCase
{
    public function testLeDiagnosticEstNonMutatifEtRetourneUnJson(): void
    {
        $calledWith = null;
        $command = new ValidationWorkflowSequenceRepairCommand(
            null,
            static function (bool $apply) use (&$calledWith): array {
                $calledWith = $apply;

                return [
                    'scanned' => 1,
                    'repairable' => 1,
                    'repaired' => 0,
                    'skipped' => 0,
                    'items' => [],
                ];
            },
        );
        $streams = $this->createIo();

        $result = $command->execute(
            new Arguments([], ['format' => 'json'], []),
            $streams['io'],
        );

        $this->assertSame(ValidationWorkflowSequenceRepairCommand::CODE_SUCCESS, $result);
        $this->assertFalse($calledWith);
        $this->assertStringContainsString('"repairable": 1', $this->streamContents($streams['out']));
    }

    public function testLesCasAmbigusRetourneUnCodeDErreur(): void
    {
        $command = new ValidationWorkflowSequenceRepairCommand(
            null,
            static fn(bool $apply): array => [
                'scanned' => 1,
                'repairable' => 0,
                'repaired' => 0,
                'skipped' => 1,
                'items' => [[
                    'step_id' => 94,
                    'matching_sequences' => 2,
                ]],
            ],
        );
        $streams = $this->createIo();

        $result = $command->execute(new Arguments([], [], []), $streams['io']);

        $this->assertSame(ValidationWorkflowSequenceRepairCommand::CODE_ERROR, $result);
        $this->assertStringContainsString('ignorée', $this->streamContents($streams['err']));
    }
}
