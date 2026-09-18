<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use Cake\Database\Exception\QueryException;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Locator\TableLocator;
use Cake\ORM\Query\SelectQuery;
use Cake\TestSuite\TestCase;

/**
 * Vérifie le workflow de validation via ses vues SQL MySQL.
 */
class ValidationWorkflowViewsTest extends TestCase
{
    /**
     * Fixtures des tables sources uniquement : les vues ne sont jamais alimentées.
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Applicationforms',
        'app.Departments',
        'app.Users',
        'app.Roles',
        'app.UserDepartments',
        'app.Contracttypes',
        'app.Hiringreasons',
        'app.Budgetfeatures',
        'app.Professionalcategories',
        'app.Worktimes',
        'app.Periods',
        'app.Yesnos',
        'app.Validations',
        'app.Validationsequences',
        'app.Validationstatuses',
        'app.ValidationWorkflowRuns',
    ];

    private TableLocator $tables;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tables = $this->getTableLocator();
    }

    /** Les vues lisent les étapes immuables et reflètent la progression puis le refus. */
    public function testLesVuesSuiventUnCycleImmuableEtRefuse(): void
    {
        $connection = ConnectionManager::get('test');
        $connection->delete('validations', []);
        $connection->delete('applicationvalidationsteps', []);
        $connection->delete('validation_workflow_runs', []);
        $connection->update('applicationforms', ['deleted' => null], ['id' => 1]);
        $connection->update('users', [
            'firstname' => 'Alice',
            'lastname' => 'Validatrice',
            'deleted' => null,
        ], ['id' => 1]);
        $connection->update('roles', ['name' => 'Responsable'], ['id' => 1]);
        $connection->insert('roles', [
            'id' => 2,
            'base' => true,
            'code' => 'DIRECTION',
            'name' => 'Direction',
            'sort' => '2',
        ]);
        $connection->insert('validation_workflow_runs', [
            'id' => 100,
            'applicationform_id' => 1,
            'started_by_user_id' => 1,
            'state' => 'en_attente',
            'started_at' => '2026-09-16 10:00:00',
            'created' => '2026-09-16 10:00:00',
            'modified' => '2026-09-16 10:00:00',
        ]);
        $connection->insert('applicationvalidationsteps', [
            'id' => 100,
            'applicationform_id' => 1,
            'validation_workflow_run_id' => 100,
            'validationsequence_id' => 1,
            'role_id' => 1,
            'sequence_number' => 1,
            'state' => 'en_attente',
            'reminder_count' => 0,
        ]);
        $connection->insert('applicationvalidationsteps', [
            'id' => 101,
            'applicationform_id' => 1,
            'validation_workflow_run_id' => 100,
            'validationsequence_id' => 1,
            'role_id' => 2,
            'sequence_number' => 2,
            'state' => 'a_venir',
            'reminder_count' => 0,
        ]);

        $this->assertSame(2, (int)$this->workflowStatus()->validationstatus_id);
        $this->assertSame(0.0, (float)$this->workflowStatus()->valid_percentage);
        $this->assertSame(1, (int)$this->currentRole()->validator_role_id);
        $this->assertCount(0, $this->visas()->all()->toList());

        $connection->update('applicationvalidationsteps', ['state' => 'acceptee'], ['id' => 100]);
        $connection->update('applicationvalidationsteps', ['state' => 'en_attente'], ['id' => 101]);
        $connection->insert('validations', [
            'id' => 100,
            'applicationform_id' => 1,
            'applicationvalidationstep_id' => 100,
            'user_id' => 1,
            'role_id' => 1,
            'validated' => '2026-09-16 11:00:00',
            'validationstatus_id' => 3,
            'is_proxy' => 0,
        ]);

        $this->assertSame(2, (int)$this->currentRole()->validator_role_id);
        $this->assertSame(50.0, (float)$this->workflowStatus()->valid_percentage);
        $this->assertSame('Alice Validatrice', $this->visas()->firstOrFail()->op_name);

        $connection->update('applicationvalidationsteps', ['state' => 'refusee'], ['id' => 101]);
        $connection->update('validation_workflow_runs', ['state' => 'refusee'], ['id' => 100]);

        $status = $this->workflowStatus();
        $this->assertSame(5, (int)$status->validationstatus_id);
        $this->assertSame(100.0, (float)$status->valid_percentage);
        $this->assertFalse((bool)$status->accepted);
        $this->assertTrue((bool)$status->rejected);
        $this->assertNull($this->currentRole());
        $this->assertCount(1, $this->visas()->all()->toList());
    }

    /**
     * La vue des rattachements reflète les lignes de ses deux tables sources.
     *
     * @return void
     */
    public function testLaVueDesRattachementsExposeLeRoleEtLeDepartement(): void
    {
        $urd = $this->tables->get('Urds')->find()
            ->where(['user_id' => 1, 'department_id' => 1])
            ->firstOrFail();

        $this->assertSame(1, (int)$urd->user_id);
        $this->assertSame(1, (int)$urd->role_id);
        $this->assertSame(1, (int)$urd->department_id);
    }

    /** Les contraintes du schéma empêchent deux cycles ou deux étapes équivalentes. */
    public function testLesContraintesDuWorkflowSontAppliquees(): void
    {
        $connection = ConnectionManager::get('test');
        $connection->delete('validations', []);
        $connection->delete('applicationvalidationsteps', []);
        $connection->delete('validation_workflow_runs', []);
        $connection->update('applicationforms', ['deleted' => null], ['id' => 1]);
        $connection->update('users', ['deleted' => null], ['id' => 1]);
        $connection->insert('validation_workflow_runs', [
            'id' => 200,
            'applicationform_id' => 1,
            'started_by_user_id' => 1,
            'state' => 'en_attente',
            'started_at' => '2026-09-16 10:00:00',
            'created' => '2026-09-16 10:00:00',
            'modified' => '2026-09-16 10:00:00',
        ]);
        $connection->insert('applicationvalidationsteps', [
            'id' => 200,
            'applicationform_id' => 1,
            'validation_workflow_run_id' => 200,
            'validationsequence_id' => 1,
            'role_id' => 1,
            'validationstatus_id' => null,
            'sequence_number' => 1,
            'state' => 'en_attente',
            'reminder_count' => 0,
        ]);
        $this->assertSame(1, $connection->execute(
            'SELECT COUNT(*) FROM applicationvalidationsteps WHERE validationstatus_id IS NULL',
        )->fetchColumn(0));

        try {
            $connection->insert('validation_workflow_runs', [
                'id' => 201,
                'applicationform_id' => 1,
                'started_by_user_id' => 1,
                'state' => 'en_attente',
                'started_at' => '2026-09-16 10:00:00',
                'created' => '2026-09-16 10:00:00',
                'modified' => '2026-09-16 10:00:00',
            ]);
            $this->fail('Un second cycle pour une même AF doit être refusé.');
        } catch (QueryException) {
            $this->addToAssertionCount(1);
        }

        try {
            $connection->insert('applicationvalidationsteps', [
                'id' => 201,
                'applicationform_id' => 1,
                'validation_workflow_run_id' => 200,
                'validationsequence_id' => 1,
                'role_id' => 1,
                'sequence_number' => 1,
                'state' => 'en_attente',
                'reminder_count' => 0,
            ]);
            $this->fail('Un rôle ne peut apparaître qu’une fois dans un cycle.');
        } catch (QueryException) {
            $this->addToAssertionCount(1);
        }
    }

    private function workflowStatus(): object
    {
        return $this->tables->get('Applicationformstatuses')->find()
            ->where(['applicationform_id' => 1])
            ->firstOrFail();
    }

    private function currentRole(): ?object
    {
        return $this->tables->get('Currentvalidationroles')->find()
            ->where(['applicationform_id' => 1])
            ->first();
    }

    private function visas(): SelectQuery
    {
        return $this->tables->get('ValidationVisas')->find()
            ->where(['applicationform_id' => 1])
            ->orderByAsc('sequence');
    }
}
