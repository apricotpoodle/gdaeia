<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Service\Workflow\ApplicationformValidationWorkflow;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/** Vérifie la persistance du lancement du cycle sur la base MySQL de test. */
class ApplicationformValidationWorkflowStartTest extends TestCase
{
    protected array $fixtures = [
        'app.Applicationforms',
        'app.Users',
        'app.Roles',
        'app.UserDepartments',
        'app.Validationsequences',
        'app.ValidationWorkflowRuns',
        'app.Applicationvalidationsteps',
        'app.Validationstatuses',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $connection = ConnectionManager::get('test');
        $connection->update('applicationforms', ['deleted' => null], ['id' => 1]);
        $connection->update('users', ['deleted' => null], ['id' => 1]);
        $connection->update('roles', ['deleted' => null, 'code' => 'adm'], ['id' => 1]);
        $connection->update('validationsequences', [
            'deleted' => null,
            'role_id' => 1,
            'sequence' => 1,
            'reminder_delay_hours' => 24,
        ], ['id' => 1]);
        $connection->delete('applicationvalidationsteps', ['validation_workflow_run_id IS NOT' => null]);
        $connection->delete('validation_workflow_runs', ['applicationform_id' => 1]);
    }

    /** Vérifie l'instantané de l'étape, son activation et son échéance au lancement. */
    public function testLeLancementCreeUneEtapeActiveAvecSonEcheanceEtSonValidateur(): void
    {
        /** @var \App\Model\Entity\Applicationform $applicationform */
        $applicationform = TableRegistry::getTableLocator()->get('Applicationforms')->get(1);
        /** @var \App\Model\Entity\User $actor */
        $actor = TableRegistry::getTableLocator()->get('Users')->get(1);
        $beforeStart = DateTime::now();

        $result = (new ApplicationformValidationWorkflow())->start($applicationform, $actor);

        $this->assertSame([], $result['issues']);
        $this->assertCount(1, $result['recipients']);
        $this->assertSame(1, (int)$result['recipients'][0]->id);
        $this->assertSame('en_attente', $result['run']->state);
        $step = TableRegistry::getTableLocator()->get('Applicationvalidationsteps')->find()
            ->where(['validation_workflow_run_id' => $result['run']->id])
            ->firstOrFail();
        $this->assertSame(1, (int)$step->validationsequence_id);
        $this->assertSame(1, (int)$step->role_id);
        $this->assertSame(1, (int)$step->sequence_number);
        $this->assertSame('en_attente', $step->state);
        $this->assertNotNull($step->activated_at);
        $this->assertNotNull($step->due_at);
        $this->assertGreaterThanOrEqual($beforeStart->addHours(24)->getTimestamp(), $step->due_at->getTimestamp());
        $this->assertLessThanOrEqual(DateTime::now()->addHours(24)->getTimestamp(), $step->due_at->getTimestamp());
    }

    /** Une règle locale arrête le snapshot à la séquence du rôle final configuré. */
    public function testLeCircuitCourtNeCopiePasLesSequencesSuivantes(): void
    {
        $connection = ConnectionManager::get('test');
        $connection->update('contracttypes', ['deleted' => null, 'code' => 'FACTURE'], ['id' => 1]);
        $connection->insert('roles', [
            'id' => 2,
            'base' => 1,
            'code' => 'ROLE_SGR',
            'name' => 'SGR',
            'sort' => 'SGR',
            'deleted' => null,
            'created' => date('Y-m-d H:i:s'),
            'modified' => date('Y-m-d H:i:s'),
        ]);
        $connection->insert('users', [
            'id' => 3,
            'username' => 'sgr-test',
            'email' => 'sgr-test@example.test',
            'password' => 'test',
            'firstname' => 'SGR',
            'lastname' => 'Test',
            'issuperuser' => 0,
            'role_id' => 2,
            'deleted' => null,
            'created' => date('Y-m-d H:i:s'),
            'modified' => date('Y-m-d H:i:s'),
        ]);
        $connection->insert('user_departments', [
            'user_id' => 3,
            'department_id' => 1,
            'created' => date('Y-m-d H:i:s'),
            'modified' => date('Y-m-d H:i:s'),
        ]);
        $connection->insert('validationsequences', [
            'id' => 2,
            'department_id' => 1,
            'name' => 'SGR',
            'description' => '',
            'role_id' => 2,
            'sequence' => 2,
            'deleted' => null,
            'reminder_delay_hours' => 24,
            'created' => date('Y-m-d H:i:s'),
            'modified' => date('Y-m-d H:i:s'),
        ]);
        $connection->update('applicationforms', ['contracttype_id' => 1], ['id' => 1]);
        $connection->insert('validation_circuit_rules', [
            'department_id' => 1,
            'contracttype_id' => 1,
            'final_role_id' => 1,
            'created' => date('Y-m-d H:i:s'),
            'modified' => date('Y-m-d H:i:s'),
        ]);

        $applicationform = TableRegistry::getTableLocator()->get('Applicationforms')->get(1);
        $actor = TableRegistry::getTableLocator()->get('Users')->get(1);
        $result = (new ApplicationformValidationWorkflow())->start($applicationform, $actor);

        $this->assertSame([], $result['issues']);
        $steps = TableRegistry::getTableLocator()->get('Applicationvalidationsteps')->find()
            ->where(['validation_workflow_run_id' => $result['run']->id])
            ->orderByAsc('sequence_number')->all()->toList();
        $this->assertCount(1, $steps);
        $this->assertSame(1, (int)$steps[0]->role_id);
    }
}
