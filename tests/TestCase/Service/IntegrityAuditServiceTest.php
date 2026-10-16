<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\Integrity\IntegrityAuditService;
use App\Service\Integrity\IntegrityCheckInterface;
use Cake\TestSuite\TestCase;

/** Vérifie l’agrégation des contrôles d’intégrité sans accès à MySQL. */
class IntegrityAuditServiceTest extends TestCase
{
    public function testAgregeTousLesControlesEtCalculeLeStatutGlobal(): void
    {
        $checks = [
            $this->check('references', 'ok', 12, 0),
            $this->check('workflow', 'error', 8, 1),
            $this->check('trees', 'warning', 4, 2),
        ];

        $report = (new IntegrityAuditService($checks))->audit();

        $this->assertFalse($report['success']);
        $this->assertSame(3, $report['summary']['checks']);
        $this->assertSame(24, $report['summary']['rows_scanned']);
        $this->assertSame(1, $report['summary']['errors']);
        $this->assertSame(2, $report['summary']['warnings']);
        $this->assertCount(3, $report['reports']);
    }

    public function testFiltreUnControleParDomaine(): void
    {
        $checks = [
            $this->check('references', 'ok', 12, 0),
            $this->check('workflow', 'ok', 8, 0),
        ];

        $report = (new IntegrityAuditService($checks))->audit('workflow');

        $this->assertTrue($report['success']);
        $this->assertSame(1, $report['summary']['checks']);
        $this->assertSame('workflow', $report['reports'][0]['id']);
    }

    /** @return \App\Service\Integrity\IntegrityCheckInterface */
    private function check(string $id, string $status, int $scanned, int $violations): IntegrityCheckInterface
    {
        return new class ($id, $status, $scanned, $violations) implements IntegrityCheckInterface {
            public function __construct(
                private readonly string $id,
                private readonly string $status,
                private readonly int $scanned,
                private readonly int $violations,
            ) {
            }

            public function id(): string
            {
                return $this->id;
            }

            /** @return array<string, mixed> */
            public function check(): array
            {
                return [
                    'id' => $this->id,
                    'status' => $this->status,
                    'rows_scanned' => $this->scanned,
                    'violations' => $this->violations,
                    'issues' => [],
                ];
            }
        };
    }
}
