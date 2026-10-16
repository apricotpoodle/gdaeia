<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Service\Integrity\ReferenceIntegrityChecker;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;

/** Vérifie les références ORM qui ne sont pas protégées par une clé SQL. */
class ReferenceIntegrityCheckerTest extends TestCase
{
    protected array $fixtures = [
        'app.Applicationforms',
        'app.Departments',
    ];

    public function testSignaleUneReferenceLogiqueAbsente(): void
    {
        $connection = ConnectionManager::get('test');
        $connection->update('applicationforms', [
            'department_id' => 999999,
            'deleted' => null,
        ], ['id' => 1]);

        $checker = new ReferenceIntegrityChecker(
            $connection,
            referenceIds: ['applicationforms.department_id'],
        );

        $report = $checker->check();

        $this->assertSame('references', $report['id']);
        $this->assertSame('error', $report['status']);
        $this->assertSame(1, $report['violations']);
        $this->assertSame('REFERENCE_ABSENTE', $report['issues'][0]['code']);
        $this->assertSame(1, $report['issues'][0]['source_id']);
        $this->assertSame(999999, $report['issues'][0]['target_id']);
    }

    public function testNeSignalePasUneReferenceHistoriqueVersUneCibleDesactivee(): void
    {
        $connection = ConnectionManager::get('test');
        $connection->update('applicationforms', [
            'department_id' => 1,
            'deleted' => '2026-07-03 09:01:24',
        ], ['id' => 1]);

        $checker = new ReferenceIntegrityChecker(
            $connection,
            referenceIds: ['applicationforms.department_id'],
        );

        $report = $checker->check();

        $this->assertSame('ok', $report['status']);
        $this->assertSame(0, $report['violations']);
    }
}
