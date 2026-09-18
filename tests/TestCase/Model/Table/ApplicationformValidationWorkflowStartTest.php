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
}
