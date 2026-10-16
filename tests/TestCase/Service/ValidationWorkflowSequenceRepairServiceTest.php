<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\ValidationWorkflowSequenceRepairService;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;

/** Vérifie le réalignement contrôlé des références de séquences orphelines. */
class ValidationWorkflowSequenceRepairServiceTest extends TestCase
{
    protected array $fixtures = [
        'app.Applicationforms',
        'app.Departments',
        'app.Roles',
        'app.Validationsequences',
        'app.Applicationvalidationsteps',
    ];

    public function testRealigneUneEtapeVersLaSequenceActiveUnique(): void
    {
        $connection = ConnectionManager::get('test');
        $connection->update('departments', ['deleted' => null], ['id' => 1]);
        $connection->update('roles', ['deleted' => null], ['id' => 1]);
        $connection->update('applicationforms', ['deleted' => null], ['id' => 1]);
        $connection->update('validationsequences', [
            'deleted' => null,
            'role_id' => 1,
            'sequence' => 1,
        ], ['id' => 1]);
        $connection->update('applicationvalidationsteps', [
            'deleted' => null,
            'validationsequence_id' => 999999,
            'role_id' => 1,
            'sequence_number' => 1,
        ], ['id' => 1]);

        $service = new ValidationWorkflowSequenceRepairService($connection);

        $report = $service->repair(true);

        $this->assertSame(1, $report['repaired']);
        $this->assertSame(1, (int)$connection->execute(
            'SELECT validationsequence_id FROM applicationvalidationsteps WHERE id = 1',
        )->fetchColumn(0));
    }

    public function testLeModeDiagnosticNeModifiePasLesEtapes(): void
    {
        $connection = ConnectionManager::get('test');
        $connection->update('departments', ['deleted' => null], ['id' => 1]);
        $connection->update('roles', ['deleted' => null], ['id' => 1]);
        $connection->update('applicationforms', ['deleted' => null], ['id' => 1]);
        $connection->update('validationsequences', ['deleted' => null], ['id' => 1]);
        $connection->update('applicationvalidationsteps', [
            'deleted' => null,
            'validationsequence_id' => 999999,
            'role_id' => 1,
            'sequence_number' => 1,
        ], ['id' => 1]);

        $service = new ValidationWorkflowSequenceRepairService($connection);

        $report = $service->repair(false);

        $this->assertSame(1, $report['repairable']);
        $this->assertSame(999999, (int)$connection->execute(
            'SELECT validationsequence_id FROM applicationvalidationsteps WHERE id = 1',
        )->fetchColumn(0));
    }
}
