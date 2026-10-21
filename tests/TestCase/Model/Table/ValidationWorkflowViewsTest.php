<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use Cake\Database\Connection;
use Cake\Database\Exception\QueryException;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Locator\TableLocator;
use Cake\ORM\Query\SelectQuery;
use Cake\TestSuite\TestCase;
use RuntimeException;

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

    /** Une demande sans cycle expose des indicateurs booléens à zéro. */
    public function testUneDemandeSansCycleExposeDesIndicateursInactifs(): void
    {
        $connection = ConnectionManager::get('test');
        $this->resetWorkflowData($connection);

        $status = $this->workflowStatus();

        $this->assertSame(0, (int)$status->en_cours);
        $this->assertSame(0, (int)$status->accepted);
        $this->assertSame(0, (int)$status->rejected);
    }

    /** Un cycle accepté ne propose plus de rôle courant et expose son visa. */
    public function testUnCycleAccepteExposeSonEtatFinalEtSonVisa(): void
    {
        $connection = ConnectionManager::get('test');
        $this->resetWorkflowData($connection);
        $this->createWorkflowRun($connection, 300, 'acceptee');
        $this->createStep($connection, 300, 300, 1, 'acceptee');
        $this->createVote($connection, 300, 1, 300, 3);

        $status = $this->workflowStatus();

        $this->assertSame(4, (int)$status->validationstatus_id);
        $this->assertFalse((bool)$status->en_cours);
        $this->assertTrue((bool)$status->accepted);
        $this->assertFalse((bool)$status->rejected);
        $this->assertNull($this->currentRole());
        $this->assertCount(1, $this->visas()->all()->toList());
    }

    /** Un cycle terminé ne propose aucun rôle même si une étape reste en attente. */
    public function testUnCycleAccepteNeProposePasDeRoleCourant(): void
    {
        $connection = ConnectionManager::get('test');
        $this->resetWorkflowData($connection);
        $this->createWorkflowRun($connection, 350, 'acceptee');
        $this->createStep($connection, 350, 350, 1, 'en_attente');

        $status = $this->workflowStatus();

        $this->assertSame(4, (int)$status->validationstatus_id);
        $this->assertFalse((bool)$status->en_cours);
        $this->assertNull($this->currentRole());
    }

    /** Un refus à la première étape clôt le cycle immédiatement. */
    public function testUnRefusDesLaPremiereEtapeClotLeCycle(): void
    {
        $connection = ConnectionManager::get('test');
        $this->resetWorkflowData($connection);
        $this->createWorkflowRun($connection, 400, 'refusee');
        $this->createStep($connection, 400, 400, 1, 'refusee');
        $this->createVote($connection, 400, 1, 400, 5);

        $status = $this->workflowStatus();

        $this->assertSame(5, (int)$status->validationstatus_id);
        $this->assertSame(100.0, (float)$status->valid_percentage);
        $this->assertFalse((bool)$status->en_cours);
        $this->assertFalse((bool)$status->accepted);
        $this->assertTrue((bool)$status->rejected);
        $this->assertNull($this->currentRole());
        $this->assertCount(1, $this->visas()->all()->toList());
    }

    /** Les étapes supprimées logiquement sont ignorées par les trois vues. */
    public function testLesVuesIgnorentUneEtapeSupprimeeLogiquement(): void
    {
        $connection = ConnectionManager::get('test');
        $this->resetWorkflowData($connection);
        $this->createWorkflowRun($connection, 500, 'en_attente');
        $this->createStep($connection, 500, 500, 1, 'en_attente', '2026-10-01 12:00:00');
        $this->createVote($connection, 500, 1, 500, 3);

        $status = $this->workflowStatus();

        $this->assertNull($status->current_sequence);
        $this->assertSame(0.0, (float)$status->valid_percentage);
        $this->assertNull($this->currentRole());
        $this->assertCount(0, $this->visas()->all()->toList());
    }

    /** Un visa incohérent entre la demande et son étape n'est exposé par aucune demande. */
    public function testUnVisaLieAUneEtapeDAutreDemandeNestPasExpose(): void
    {
        $connection = ConnectionManager::get('test');
        $this->resetWorkflowData($connection);
        $this->createSecondApplicationform($connection);
        $this->createWorkflowRun($connection, 600, 'acceptee');
        $this->createStep($connection, 600, 600, 2, 'acceptee');
        $this->createVote($connection, 600, 1, 600, 3);

        $this->assertCount(0, $this->visas(1)->all()->toList());
        $this->assertCount(0, $this->visas(2)->all()->toList());
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

    private function workflowStatus(int $applicationformId = 1): object
    {
        return $this->tables->get('Applicationformstatuses')->find()
            ->where(['applicationform_id' => $applicationformId])
            ->firstOrFail();
    }

    private function currentRole(int $applicationformId = 1): ?object
    {
        return $this->tables->get('Currentvalidationroles')->find()
            ->where(['applicationform_id' => $applicationformId])
            ->first();
    }

    private function visas(int $applicationformId = 1): SelectQuery
    {
        return $this->tables->get('ValidationVisas')->find()
            ->where(['applicationform_id' => $applicationformId])
            ->orderByAsc('sequence');
    }

    /** Réinitialise les lignes des tables de workflow manipulées par les scénarios. */
    private function resetWorkflowData(Connection $connection): void
    {
        $connection->delete('validations', []);
        $connection->delete('applicationvalidationsteps', []);
        $connection->delete('validation_workflow_runs', []);
        $connection->update('applicationforms', ['deleted' => null], ['id' => 1]);
        $connection->update('users', ['deleted' => null], ['id' => 1]);
    }

    /** Insère un cycle avec l'état requis par le scénario. */
    private function createWorkflowRun(Connection $connection, int $id, string $state): void
    {
        $connection->insert('validation_workflow_runs', [
            'id' => $id,
            'applicationform_id' => 1,
            'started_by_user_id' => 1,
            'state' => $state,
            'started_at' => '2026-09-16 10:00:00',
            'created' => '2026-09-16 10:00:00',
            'modified' => '2026-09-16 10:00:00',
        ]);
    }

    /** Insère une étape figée dans le cycle demandé. */
    private function createStep(
        Connection $connection,
        int $id,
        int $runId,
        int $applicationformId,
        string $state,
        ?string $deleted = null,
    ): void {
        $connection->insert('applicationvalidationsteps', [
            'id' => $id,
            'applicationform_id' => $applicationformId,
            'validation_workflow_run_id' => $runId,
            'validationsequence_id' => 1,
            'role_id' => 1,
            'sequence_number' => 1,
            'state' => $state,
            'deleted' => $deleted,
            'reminder_count' => 0,
        ]);
    }

    /** Insère un vote rattaché à une étape. */
    private function createVote(
        Connection $connection,
        int $id,
        int $applicationformId,
        int $stepId,
        int $validationstatusId,
    ): void {
        $connection->insert('validations', [
            'id' => $id,
            'applicationform_id' => $applicationformId,
            'applicationvalidationstep_id' => $stepId,
            'user_id' => 1,
            'role_id' => 1,
            'validated' => '2026-09-16 11:00:00',
            'validationstatus_id' => $validationstatusId,
            'is_proxy' => 0,
        ]);
    }

    /** Clone la demande fixture pour créer une seconde clé étrangère valide. */
    private function createSecondApplicationform(Connection $connection): void
    {
        $applicationform = $connection->execute(
            'SELECT * FROM applicationforms WHERE id = 1',
        )->fetch('assoc');
        if (!is_array($applicationform)) {
            throw new RuntimeException('La demande fixture 1 est introuvable.');
        }

        $applicationform['id'] = 2;
        $applicationform['deleted'] = null;
        $connection->insert('applicationforms', $applicationform);
    }
}
