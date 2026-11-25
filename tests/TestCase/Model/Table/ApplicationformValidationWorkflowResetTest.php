<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Service\Workflow\ApplicationformValidationWorkflow;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use RuntimeException;

/** Vérifie que la remise à zéro efface uniquement les données créées par un cycle. */
class ApplicationformValidationWorkflowResetTest extends TestCase
{
    private const WORKFLOW_TEST_ID = 900_001;
    private const FOREIGN_VALIDATION_TEST_ID = 900_002;
    private const FUTURE_STEP_TEST_ID = 900_003;
    private const UNRELATED_STEP_TEST_ID = 900_004;
    private const PARALLEL_FINAL_STEP_TEST_ID = 900_005;

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
        $connection->delete('validations', ['id IN' => [self::WORKFLOW_TEST_ID, self::FOREIGN_VALIDATION_TEST_ID]]);
        $connection->delete('applicationvalidationsteps', ['id IN' => [
            self::WORKFLOW_TEST_ID,
            self::FUTURE_STEP_TEST_ID,
            self::UNRELATED_STEP_TEST_ID,
            self::PARALLEL_FINAL_STEP_TEST_ID,
        ]]);
        $connection->delete('validation_workflow_runs', ['id' => self::WORKFLOW_TEST_ID]);

        parent::tearDown();
    }

    public function testLaRemiseAZeroSupprimeVotesEtEtapesDuCycleUniquement(): void
    {
        $connection = ConnectionManager::get('test');
        $now = '2026-09-17 12:00:00';
        $connection->insert('validation_workflow_runs', [
            'id' => self::WORKFLOW_TEST_ID,
            'applicationform_id' => 1,
            'started_by_user_id' => 1,
            'state' => 'en_attente',
            'started_at' => $now,
            'created' => $now,
            'modified' => $now,
        ]);
        $connection->insert('validationsequences', [
            'id' => 2,
            'department_id' => 1,
            'role_id' => 2,
            'sequence' => 3,
            'reminder_delay_hours' => 12,
            'deleted' => null,
            'created' => $now,
            'modified' => $now,
        ]);
        $connection->insert('applicationvalidationsteps', [
            'id' => self::WORKFLOW_TEST_ID,
            'applicationform_id' => 1,
            'validation_workflow_run_id' => self::WORKFLOW_TEST_ID,
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
        $connection->insert('applicationvalidationsteps', [
            'id' => self::FUTURE_STEP_TEST_ID,
            'applicationform_id' => 1,
            'validation_workflow_run_id' => self::WORKFLOW_TEST_ID,
            'validationsequence_id' => 1,
            'role_id' => 2,
            'sequence_number' => 2,
            'state' => 'a_venir',
            'activated_at' => null,
            'reminder_count' => 0,
            'created' => $now,
            'modified' => $now,
        ]);
        $connection->insert('validations', [
            'id' => self::WORKFLOW_TEST_ID,
            'applicationform_id' => 1,
            'applicationvalidationstep_id' => self::WORKFLOW_TEST_ID,
            'user_id' => 1,
            'role_id' => 1,
            'validationstatus_id' => 1,
            'validated' => $now,
            'is_proxy' => 0,
            'created' => $now,
            'modified' => $now,
        ]);
        $connection->insert('validations', [
            'id' => self::FOREIGN_VALIDATION_TEST_ID,
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

        $this->assertSame(['run_id' => self::WORKFLOW_TEST_ID, 'validations' => 1, 'steps' => 2, 'runs' => 1], $deleted);
        $this->assertSame(0, (int)$connection->execute('SELECT COUNT(*) FROM validation_workflow_runs WHERE id = ' . self::WORKFLOW_TEST_ID)->fetchColumn(0));
        $this->assertSame(0, (int)$connection->execute('SELECT COUNT(*) FROM applicationvalidationsteps WHERE id = ' . self::WORKFLOW_TEST_ID)->fetchColumn(0));
        $this->assertSame(0, (int)$connection->execute('SELECT COUNT(*) FROM validations WHERE id = ' . self::WORKFLOW_TEST_ID)->fetchColumn(0));
        $this->assertSame(1, (int)$connection->execute('SELECT COUNT(*) FROM validations WHERE id = ' . self::FOREIGN_VALIDATION_TEST_ID)->fetchColumn(0));
        $this->assertSame(1, (int)$connection->execute('SELECT COUNT(*) FROM validations WHERE id = 1')->fetchColumn(0));
    }

    public function testLaRemiseAZeroRefuseLeCycleDontLaDerniereSequenceEstOuverte(): void
    {
        $connection = ConnectionManager::get('test');
        $now = '2026-09-17 12:00:00';
        $connection->insert('validation_workflow_runs', [
            'id' => self::WORKFLOW_TEST_ID,
            'applicationform_id' => 1,
            'started_by_user_id' => 1,
            'state' => 'en_attente',
            'started_at' => $now,
            'created' => $now,
            'modified' => $now,
        ]);
        foreach (
            [
                [self::WORKFLOW_TEST_ID, 1, 'acceptee'],
                [self::FUTURE_STEP_TEST_ID, 2, 'a_venir'],
            ] as [$id, $sequence, $state]
        ) {
            $connection->insert('applicationvalidationsteps', [
                'id' => $id,
                'applicationform_id' => 1,
                'validation_workflow_run_id' => self::WORKFLOW_TEST_ID,
                'validationsequence_id' => 1,
                'role_id' => $sequence,
                'sequence_number' => $sequence,
                'state' => $state,
                'activated_at' => $sequence === 1 ? $now : null,
                'completed_at' => $state === 'acceptee' ? $now : null,
                'reminder_count' => 0,
                'created' => $now,
                'modified' => $now,
            ]);
        }
        $connection->insert('applicationvalidationsteps', [
            'id' => self::PARALLEL_FINAL_STEP_TEST_ID,
            'applicationform_id' => 1,
            'validation_workflow_run_id' => self::WORKFLOW_TEST_ID,
            'validationsequence_id' => 1,
            'role_id' => 3,
            'sequence_number' => 2,
            'state' => 'a_venir',
            'activated_at' => null,
            'reminder_count' => 0,
            'created' => $now,
            'modified' => $now,
        ]);
        $connection->insert('applicationvalidationsteps', [
            'id' => self::UNRELATED_STEP_TEST_ID,
            'applicationform_id' => 1,
            'validation_workflow_run_id' => null,
            'validationsequence_id' => 1,
            'role_id' => 3,
            'sequence_number' => 99,
            'state' => 'a_venir',
            'activated_at' => null,
            'reminder_count' => 0,
            'created' => $now,
            'modified' => $now,
        ]);
        $connection->insert('validations', [
            'id' => self::WORKFLOW_TEST_ID,
            'applicationform_id' => 1,
            'applicationvalidationstep_id' => self::WORKFLOW_TEST_ID,
            'user_id' => 1,
            'role_id' => 1,
            'validationstatus_id' => 1,
            'validated' => $now,
            'is_proxy' => 0,
            'created' => $now,
            'modified' => $now,
        ]);

        $applicationform = TableRegistry::getTableLocator()->get('Applicationforms')->get(1);
        $workflow = new ApplicationformValidationWorkflow();
        $this->assertTrue($workflow->canReset($applicationform));
        $connection->update('applicationvalidationsteps', [
            'state' => 'en_attente',
            'activated_at' => $now,
        ], ['id' => self::FUTURE_STEP_TEST_ID]);

        try {
            $workflow->reset($applicationform);
            $this->fail('La dernière séquence ouverte doit empêcher la remise à zéro.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('dernière séquence', $exception->getMessage());
        }

        $this->assertSame(1, (int)$connection->execute('SELECT COUNT(*) FROM validation_workflow_runs WHERE id = ' . self::WORKFLOW_TEST_ID)->fetchColumn(0));
        $this->assertSame(3, (int)$connection->execute('SELECT COUNT(*) FROM applicationvalidationsteps WHERE validation_workflow_run_id = ' . self::WORKFLOW_TEST_ID)->fetchColumn(0));
        $this->assertSame(1, (int)$connection->execute('SELECT COUNT(*) FROM validations WHERE applicationvalidationstep_id = ' . self::WORKFLOW_TEST_ID)->fetchColumn(0));
    }

    /** Vérifie que la suppression de la demande réutilise la purge transactionnelle du cycle. */
    public function testLaSuppressionDeDemandePurgeSonCycleSansToucherLesVotesEtrangers(): void
    {
        $connection = ConnectionManager::get('test');
        $now = '2026-09-17 12:00:00';
        $connection->insert('validation_workflow_runs', [
            'id' => self::WORKFLOW_TEST_ID, 'applicationform_id' => 1, 'started_by_user_id' => 1,
            'state' => 'en_attente', 'started_at' => $now, 'created' => $now, 'modified' => $now,
        ]);
        $connection->insert('applicationvalidationsteps', [
            'id' => self::WORKFLOW_TEST_ID, 'applicationform_id' => 1, 'validation_workflow_run_id' => self::WORKFLOW_TEST_ID,
            'validationsequence_id' => 1, 'role_id' => 1, 'sequence_number' => 1,
            'state' => 'en_attente', 'reminder_count' => 0, 'created' => $now, 'modified' => $now,
        ]);
        $connection->insert('validations', [
            'id' => self::WORKFLOW_TEST_ID, 'applicationform_id' => 1, 'applicationvalidationstep_id' => self::WORKFLOW_TEST_ID,
            'user_id' => 1, 'role_id' => 1, 'validated' => $now, 'is_proxy' => 0,
            'created' => $now, 'modified' => $now,
        ]);
        $connection->insert('validations', [
            'id' => self::FOREIGN_VALIDATION_TEST_ID, 'applicationform_id' => 2, 'applicationvalidationstep_id' => null,
            'user_id' => 1, 'role_id' => 1, 'validated' => $now, 'is_proxy' => 0,
            'created' => $now, 'modified' => $now,
        ]);

        $applicationform = TableRegistry::getTableLocator()->get('Applicationforms')->get(1);
        $deleted = (new ApplicationformValidationWorkflow())->deleteApplicationform($applicationform);

        $this->assertSame(['validations' => 2, 'steps' => 2, 'runs' => 1], $deleted);
        $this->assertSame(0, (int)$connection->execute('SELECT COUNT(*) FROM applicationforms WHERE id = 1')->fetchColumn(0));
        $this->assertSame(0, (int)$connection->execute('SELECT COUNT(*) FROM applicationvalidationsteps WHERE applicationform_id = 1')->fetchColumn(0));
        $this->assertSame(0, (int)$connection->execute('SELECT COUNT(*) FROM validations WHERE applicationform_id = 1')->fetchColumn(0));
        $this->assertSame(1, (int)$connection->execute('SELECT COUNT(*) FROM validations WHERE id = ' . self::FOREIGN_VALIDATION_TEST_ID)->fetchColumn(0));
    }
}
