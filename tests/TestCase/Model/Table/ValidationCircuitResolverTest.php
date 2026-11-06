<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\Applicationform;
use App\Service\Workflow\ApplicationformValidationWorkflow;
use App\Service\Workflow\ValidationCircuitResolver;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/** Vérifie la résolution des circuits locaux sur les référentiels MySQL réels. */
class ValidationCircuitResolverTest extends TestCase
{
    protected array $fixtures = [
        'app.Applicationforms', 'app.Departments', 'app.Contracttypes', 'app.Users',
        'app.Roles', 'app.UserDepartments', 'app.Validationsequences',
        'app.ValidationWorkflowRuns', 'app.Applicationvalidationsteps', 'app.Validations',
        'app.Validationstatuses', 'app.WorkflowSettings', 'app.ValidationCircuitRules',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $connection = ConnectionManager::get('test');
        $connection->update('departments', ['deleted' => null, 'name' => 'Rédaction SEM'], ['id' => 1]);
        $connection->update('contracttypes', ['deleted' => null, 'name' => 'Facture'], ['id' => 1]);
        $connection->update('roles', ['deleted' => null, 'code' => 'dem', 'name' => 'Demandeur'], ['id' => 1]);
        $connection->update('users', ['deleted' => null], ['id' => 1]);
        $connection->update('validationsequences', ['deleted' => null, 'sequence' => 1], ['id' => 1]);
        $connection->update('applicationforms', ['deleted' => null, 'contracttype_id' => 1], ['id' => 1]);
        foreach ([3, 5] as $statusId) {
            $connection->insert('validationstatuses', ['id' => $statusId, 'code' => 'statut-' . $statusId, 'name' => 'Statut ' . $statusId, 'created' => '2026-11-06 10:00:00', 'modified' => '2026-11-06 10:00:00']);
        }
        foreach ([2 => ['sgr', 'SGR', 2], 3 => ['cdg', 'Contrôle de gestion', 2], 4 => ['drh', 'DRH', 3]] as $id => $data) {
            $connection->insert('roles', [
                'id' => $id, 'base' => 1, 'code' => $data[0], 'name' => $data[1], 'sort' => $data[1],
                'created' => '2026-11-06 10:00:00', 'modified' => '2026-11-06 10:00:00',
            ]);
            $connection->insert('validationsequences', [
                'id' => $id, 'department_id' => 1, 'role_id' => $id, 'name' => $data[1],
                'sequence' => $data[2], 'reminder_delay_hours' => 24,
                'created' => '2026-11-06 10:00:00', 'modified' => '2026-11-06 10:00:00',
            ]);
            $connection->insert('users', [
                'id' => $id + 1, 'username' => 'validateur-' . $id, 'email' => 'validateur-' . $id . '@example.test',
                'password' => 'test', 'firstname' => $data[1], 'lastname' => 'Test', 'role_id' => $id,
                'issuperuser' => 0, 'created' => '2026-11-06 10:00:00', 'modified' => '2026-11-06 10:00:00',
            ]);
            $connection->insert('user_departments', ['user_id' => $id + 1, 'department_id' => 1, 'created' => '2026-11-06 10:00:00', 'modified' => '2026-11-06 10:00:00']);
        }
    }

    public function testSansRegleLeCircuitCompletEstConserve(): void
    {
        $result = (new ValidationCircuitResolver())->resolve(1, 1, applicationform: $this->applicationform());

        $this->assertSame([], $result['issues']);
        $this->assertSame([1, 2, 3, 4], array_column($result['sequences'], 'role_id'));
    }

    public function testLaCoupureConserveTousLesRolesParallelesEtIgnoreLEligibiliteDesEtapesSuivantes(): void
    {
        ConnectionManager::get('test')->delete('user_departments', ['user_id' => 5]);

        $result = (new ValidationCircuitResolver())->resolve(1, 1, 2, $this->applicationform());

        $this->assertSame([], $result['issues']);
        $this->assertSame([1, 2, 3], array_column($result['sequences'], 'role_id'));
    }

    public function testLaReglePersistanteNeConcerneQueSonDepartementEtSonContrat(): void
    {
        $connection = ConnectionManager::get('test');
        $connection->insert('validation_circuit_rules', ['department_id' => 2, 'contracttype_id' => 1, 'final_role_id' => 1, 'created' => '2026-11-06 10:00:00', 'modified' => '2026-11-06 10:00:00']);
        $otherDepartment = (new ValidationCircuitResolver())->resolve(1, 1, applicationform: $this->applicationform());
        $this->assertSame([1, 2, 3, 4], array_column($otherDepartment['sequences'], 'role_id'));
        $connection->update('validation_circuit_rules', ['department_id' => 1], ['department_id' => 2]);

        $otherContract = (new ValidationCircuitResolver())->resolve(1, 2, applicationform: $this->applicationform());
        $localContract = (new ValidationCircuitResolver())->resolve(1, 1, applicationform: $this->applicationform());

        $this->assertSame([1, 2, 3, 4], array_column($otherContract['sequences'], 'role_id'));
        $this->assertSame([1], array_column($localContract['sequences'], 'role_id'));
    }

    public function testUnRoleFinalInactifBloqueAvecLesTroisLibellesMetier(): void
    {
        ConnectionManager::get('test')->update('roles', ['deleted' => '2026-11-06 11:00:00'], ['id' => 2]);

        $result = (new ValidationCircuitResolver())->resolve(1, 1, 2, $this->applicationform());

        $this->assertNotEmpty($result['issues']);
        $this->assertStringContainsString('Facture', $result['issues'][0]);
        $this->assertStringContainsString('Rédaction SEM', $result['issues'][0]);
        $this->assertStringContainsString('SGR', $result['issues'][0]);
    }

    public function testUneSequenceFinaleInactiveBloqueSansRepliSurLeCircuitComplet(): void
    {
        ConnectionManager::get('test')->update('validationsequences', ['deleted' => '2026-11-06 11:00:00'], ['id' => 2]);

        $result = (new ValidationCircuitResolver())->resolve(1, 1, 2, $this->applicationform());

        $this->assertNotEmpty($result['issues']);
        $this->assertSame([], $result['sequences']);
    }

    #[DataProvider('rolesInactifsRetenus')]
    public function testUnRoleRetenuInactifBloqueSansSupprimerSonAffectationNiCreerDeCycle(
        int $inactiveRoleId,
        ?int $finalRoleId,
        string $roleName,
        array $retainedRoleIds,
    ): void {
        $connection = ConnectionManager::get('test');
        $connection->update('roles', ['deleted' => '2026-11-06 11:00:00'], ['id' => $inactiveRoleId]);
        if ($finalRoleId !== null) {
            $connection->insert('validation_circuit_rules', [
                'department_id' => 1, 'contracttype_id' => 1, 'final_role_id' => $finalRoleId,
                'created' => '2026-11-06 10:00:00', 'modified' => '2026-11-06 10:00:00',
            ]);
        }

        $result = (new ValidationCircuitResolver())->resolve(1, 1, $finalRoleId, $this->applicationform());

        $this->assertCount(1, $result['issues']);
        $this->assertStringContainsString($roleName, $result['issues'][0]);
        $this->assertStringContainsString('Facture', $result['issues'][0]);
        $this->assertStringContainsString('Rédaction SEM', $result['issues'][0]);
        $this->assertSame($retainedRoleIds, array_column($result['sequences'], 'role_id'));

        $launch = (new ApplicationformValidationWorkflow())->start(
            $this->getTableLocator()->get('Applicationforms')->get(1),
            $this->getTableLocator()->get('Users')->get(1),
        );

        $this->assertNotEmpty($launch['issues']);
        $this->assertNull($launch['run']);
        $this->assertSame(0, $this->getTableLocator()->get('ValidationWorkflowRuns')->find()->count());
        $this->assertSame(0, $this->getTableLocator()->get('Applicationvalidationsteps')->find()
            ->where(['validation_workflow_run_id IS NOT' => null])->count());
    }

    /** @return array<string, array{int, int|null, string, list<int>}> */
    public static function rolesInactifsRetenus(): array
    {
        return [
            'rôle parallèle final' => [3, 2, 'Contrôle de gestion', [1, 2, 3]],
            'rôle intermédiaire' => [2, 4, 'SGR', [1, 2, 3, 4]],
            'circuit complet sans règle' => [4, null, 'DRH', [1, 2, 3, 4]],
        ];
    }

    public function testUnRoleInactifStrictementApresLaCoupureNeBloquePasLeCircuitCourt(): void
    {
        $connection = ConnectionManager::get('test');
        $connection->update('roles', ['deleted' => '2026-11-06 11:00:00'], ['id' => 4]);
        $connection->insert('validation_circuit_rules', [
            'department_id' => 1, 'contracttype_id' => 1, 'final_role_id' => 2,
            'created' => '2026-11-06 10:00:00', 'modified' => '2026-11-06 10:00:00',
        ]);

        $resolved = (new ValidationCircuitResolver())->resolve(1, 1, 2, $this->applicationform());
        $launch = (new ApplicationformValidationWorkflow())->start(
            $this->getTableLocator()->get('Applicationforms')->get(1),
            $this->getTableLocator()->get('Users')->get(1),
        );

        $this->assertSame([], $resolved['issues']);
        $this->assertSame([1, 2, 3], array_column($resolved['sequences'], 'role_id'));
        $this->assertSame([], $launch['issues']);
        $this->assertNotNull($launch['run']);
        $this->assertSame(3, $this->getTableLocator()->get('Applicationvalidationsteps')->find()
            ->where(['validation_workflow_run_id' => $launch['run']->id])->count());
    }

    public function testUnValidateurRattacheAUnAutreDepartementNePermetPasLeLancement(): void
    {
        ConnectionManager::get('test')->update('user_departments', ['department_id' => 2], ['user_id' => 3]);

        $result = (new ValidationCircuitResolver())->resolve(1, 1, 2, $this->applicationform());

        $this->assertCount(1, $result['issues']);
        $this->assertStringContainsString('SGR', $result['issues'][0]);
        $this->assertStringContainsString('Rédaction SEM', $result['issues'][0]);
    }

    public function testLeDemandeurSeResoutParCodeSansExigerDeRattachementNiSonRoleCourant(): void
    {
        $connection = ConnectionManager::get('test');
        $connection->delete('user_departments', ['user_id' => 1]);
        $connection->update('users', ['role_id' => 4], ['id' => 1]);
        $result = (new ValidationCircuitResolver())->resolve(1, 1, 1, $this->applicationform());

        $this->assertSame([], $result['issues']);
        $this->assertSame([1], array_column($result['sequences'], 'role_id'));
    }

    public function testLaConfigurationNeBloquePasSurUnDemandeurEncoreInconnu(): void
    {
        ConnectionManager::get('test')->delete('user_departments', ['department_id' => 1]);

        $result = (new ValidationCircuitResolver())->resolve(1, 1, 1);

        $this->assertSame([], $result['issues']);
        $this->assertSame([1], array_column($result['sequences'], 'role_id'));
    }

    public function testUnDemandeurSupprimeBloqueLeLancementSansCreerDeCyclePartiel(): void
    {
        ConnectionManager::get('test')->update('users', ['deleted' => '2026-11-06 11:00:00'], ['id' => 1]);
        $forms = $this->getTableLocator()->get('Applicationforms');
        $actor = $this->getTableLocator()->get('Users')->get(2);

        $result = (new ApplicationformValidationWorkflow())->start($forms->get(1), $actor);

        $this->assertNotEmpty($result['issues']);
        $this->assertNull($result['run']);
        $this->assertSame(0, $this->getTableLocator()->get('ValidationWorkflowRuns')->find()->count());
        $this->assertSame(0, $this->getTableLocator()->get('Applicationvalidationsteps')->find()
            ->where(['validation_workflow_run_id IS NOT' => null])->count());
    }

    public function testLeCycleCourtConserveSonInstantaneEtSesDestinatairesApresReconfiguration(): void
    {
        $connection = ConnectionManager::get('test');
        $connection->insert('validation_circuit_rules', ['department_id' => 1, 'contracttype_id' => 1, 'final_role_id' => 2, 'created' => '2026-11-06 10:00:00', 'modified' => '2026-11-06 10:00:00']);
        $workflow = new ApplicationformValidationWorkflow();
        $form = $this->getTableLocator()->get('Applicationforms')->get(1);
        $actor = $this->getTableLocator()->get('Users')->get(1);
        $result = $workflow->start($form, $actor);
        $this->assertSame([], $result['issues']);
        $steps = $this->getTableLocator()->get('Applicationvalidationsteps')->find()
            ->where(['validation_workflow_run_id' => $result['run']->id])
            ->orderByAsc('sequence_number')->orderByAsc('role_id')->all()->toList();
        $this->assertCount(3, $steps);
        $connection->update('validation_circuit_rules', ['final_role_id' => 4], ['department_id' => 1]);
        $connection->delete('validation_circuit_rules', ['department_id' => 1]);
        $connection->update('validationsequences', ['sequence' => 8], ['id' => 2]);
        $connection->update('validationsequences', ['deleted' => '2026-11-06 11:00:00'], ['id' => 3]);

        $requesterVote = $workflow->vote($form, $actor, (int)$steps[0]->id, true, '', false);
        $this->assertSame([3, 4], array_column($requesterVote['nextRecipients'], 'id'));
        $snapshot = $this->getTableLocator()->get('Applicationvalidationsteps')->find()
            ->where(['validation_workflow_run_id' => $result['run']->id])
            ->orderByAsc('sequence_number')->orderByAsc('role_id')->all()->toList();
        $this->assertSame([1, 2, 3], array_column($snapshot, 'role_id'));
        $this->assertSame([1, 2, 2], array_column($snapshot, 'sequence_number'));
        $firstParallel = $workflow->vote($form, $this->getTableLocator()->get('Users')->get(3), (int)$steps[1]->id, true, '', false);
        $this->assertFalse($firstParallel['final']);
        $this->assertSame([], $firstParallel['nextRecipients']);

        $lastParallel = $workflow->vote($form, $this->getTableLocator()->get('Users')->get(4), (int)$steps[2]->id, true, '', false);

        $this->assertTrue($lastParallel['final']);
        $this->assertSame('acceptee', $lastParallel['state']);
        $this->assertSame([], $lastParallel['nextRecipients']);
    }

    public function testLeRefusDUnRoleParalleleClotLeCycleCourtSansDestinataireSuivant(): void
    {
        ConnectionManager::get('test')->insert('validation_circuit_rules', [
            'department_id' => 1, 'contracttype_id' => 1, 'final_role_id' => 2,
            'created' => '2026-11-06 10:00:00', 'modified' => '2026-11-06 10:00:00',
        ]);
        $workflow = new ApplicationformValidationWorkflow();
        $form = $this->getTableLocator()->get('Applicationforms')->get(1);
        $actor = $this->getTableLocator()->get('Users')->get(1);
        $result = $workflow->start($form, $actor);
        $steps = $this->getTableLocator()->get('Applicationvalidationsteps')->find()
            ->where(['validation_workflow_run_id' => $result['run']->id])
            ->orderByAsc('sequence_number')->orderByAsc('role_id')->all()->toList();
        $workflow->vote($form, $actor, (int)$steps[0]->id, true, '', false);

        $rejection = $workflow->vote(
            $form,
            $this->getTableLocator()->get('Users')->get(3),
            (int)$steps[1]->id,
            false,
            'Budget à corriger',
            false,
        );

        $this->assertTrue($rejection['final']);
        $this->assertSame('refusee', $rejection['state']);
        $this->assertSame([], $rejection['nextRecipients']);
        $this->assertSame('annulee', $this->getTableLocator()->get('Applicationvalidationsteps')->get($steps[2]->id)->state);
        $this->assertSame([1, 2, 3], array_column($steps, 'role_id'));
    }

    private function applicationform(): Applicationform
    {
        return new Applicationform(['department_id' => 1, 'contracttype_id' => 1, 'user_id' => 1]);
    }
}
