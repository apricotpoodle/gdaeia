<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Service\Workflow\ApplicationformValidationWorkflow;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/** Vérifie que la remise à zéro efface uniquement les données créées par un cycle. */
class ApplicationformValidationWorkflowResetTest extends TestCase
{
    protected array $fixtures = [
        'app.Applicationforms',
        'app.Users',
        'app.Roles',
        'app.Validationsequences',
        'app.Validationstatuses',
        'app.ValidationWorkflowRuns',
        'app.Applicationvalidationsteps',
        'app.Validations',
    ];

    protected function tearDown(): void
    {
        $connection = ConnectionManager::get('test');
        $connection->delete('validations', ['id IN' => [900, 901]]);
        $connection->delete('applicationvalidationsteps', ['id' => 900]);
        $connection->delete('validation_workflow_runs', ['id' => 900]);

        parent::tearDown();
    }

    public function testLaRemiseAZeroSupprimeVotesEtEtapesDuCycleUniquement(): void
    {
        $connection = ConnectionManager::get('test');
        $now = '2026-09-17 12:00:00';
        $connection->insert('validation_workflow_runs', [
            'id' => 900,
            'applicationform_id' => 1,
            'started_by_user_id' => 1,
            'state' => 'en_attente',
            'started_at' => $now,
            'created' => $now,
            'modified' => $now,
        ]);
        $connection->insert('applicationvalidationsteps', [
            'id' => 900,
            'applicationform_id' => 1,
            'validation_workflow_run_id' => 900,
            'validationsequence_id' => 1,
            'role_id' => 1,
            'sequence_number' => 1,
            'validationstatus_id' => 1,
            'state' => 'acceptee',
            'completed_at' => $now,
            'reminder_count' => 0,
            'created' => $now,
            'modified' => $now,
        ]);
        $connection->insert('validations', [
            'id' => 900,
            'applicationform_id' => 1,
            'applicationvalidationstep_id' => 900,
            'user_id' => 1,
            'role_id' => 1,
            'validationstatus_id' => 1,
            'validated' => $now,
            'is_proxy' => 0,
            'created' => $now,
            'modified' => $now,
        ]);
        $connection->insert('validations', [
            'id' => 901,
            'applicationform_id' => 1,
            'applicationvalidationstep_id' => null,
            'user_id' => 1,
            'role_id' => 1,
            'validationstatus_id' => 1,
            'validated' => $now,
            'is_proxy' => 0,
            'created' => $now,
            'modified' => $now,
        ]);

        $applicationform = TableRegistry::getTableLocator()->get('Applicationforms')->get(1);
        $deleted = (new ApplicationformValidationWorkflow())->reset($applicationform);

        $this->assertSame(['run_id' => 900, 'validations' => 1, 'steps' => 1, 'runs' => 1], $deleted);
        $this->assertSame(0, (int)$connection->execute('SELECT COUNT(*) FROM validation_workflow_runs WHERE id = 900')->fetchColumn(0));
        $this->assertSame(0, (int)$connection->execute('SELECT COUNT(*) FROM applicationvalidationsteps WHERE id = 900')->fetchColumn(0));
        $this->assertSame(0, (int)$connection->execute('SELECT COUNT(*) FROM validations WHERE id = 900')->fetchColumn(0));
        $this->assertSame(1, (int)$connection->execute('SELECT COUNT(*) FROM validations WHERE id = 901')->fetchColumn(0));
    }
}
