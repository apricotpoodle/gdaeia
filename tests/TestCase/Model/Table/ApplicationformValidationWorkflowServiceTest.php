<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Service\Workflow\ApplicationformValidationWorkflow;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use RuntimeException;

/** Vérifie les transactions du service de workflow sur la base MySQL de test. */
class ApplicationformValidationWorkflowServiceTest extends TestCase
{
    protected array $fixtures = [
        'app.Applicationforms',
        'app.Users',
        'app.Roles',
        'app.UserDepartments',
        'app.Validationsequences',
        'app.Validationstatuses',
        'app.ValidationWorkflowRuns',
        'app.Applicationvalidationsteps',
        'app.Validations',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $connection = ConnectionManager::get('test');
        $now = '2026-09-17 12:00:00';
        $connection->update('applicationforms', ['deleted' => null], ['id' => 1]);
        $connection->update('users', ['role_id' => 2, 'deleted' => null], ['id' => 2]);
        $connection->insert('roles', [
            'id' => 2,
            'base' => 0,
            'code' => 'val',
            'name' => 'Validateur de test',
            'sort' => 'Validateur de test',
            'deleted' => null,
            'created' => $now,
            'modified' => $now,
        ]);
        $connection->insert('user_departments', [
            'id' => 2,
            'user_id' => 2,
            'department_id' => 1,
            'created' => $now,
            'modified' => $now,
        ]);
        $connection->update('validationsequences', [
            'role_id' => 2,
            'sequence' => 1,
            'deleted' => null,
            'reminder_delay_hours' => 24,
        ], ['id' => 1]);
        foreach ([3, 5] as $id) {
            $connection->insert('validationstatuses', [
                'id' => $id,
                'code' => 'test-' . $id,
                'name' => 'Statut de test ' . $id,
                'deleted' => null,
                'created' => $now,
                'modified' => $now,
            ]);
        }
    }

    protected function tearDown(): void
    {
        $connection = ConnectionManager::get('test');
        $connection->delete('validations', ['applicationform_id' => 1]);
        $connection->delete('applicationvalidationsteps', ['applicationform_id' => 1]);
        $connection->delete('validation_workflow_runs', ['applicationform_id' => 1]);
        parent::tearDown();
    }

    public function testLAcceptationDeLaDerniereEtapeClotureLeCycle(): void
    {
        [$applicationform, $actor, $workflow] = $this->workflowContext();

        $started = $workflow->start($applicationform, $actor);
        $this->assertSame([], $started['issues']);
        $this->assertCount(1, $started['recipients']);
        $this->assertSame(2, (int)$started['recipients'][0]->id);
        $this->assertSame('en_attente', $started['run']->state);

        $step = TableRegistry::getTableLocator()->get('Applicationvalidationsteps')->find()
            ->where(['validation_workflow_run_id' => $started['run']->id])
            ->firstOrFail();
        $voted = $workflow->vote($applicationform, $actor, (int)$step->id, true, 'Accord.', false);

        $this->assertSame('acceptee', $voted['state']);
        $this->assertTrue($voted['final']);
        $this->assertSame(1, TableRegistry::getTableLocator()->get('Validations')->find()
            ->where(['applicationvalidationstep_id' => $step->id, 'validationstatus_id' => 3])
            ->count());
        $persistedStep = TableRegistry::getTableLocator()->get('Applicationvalidationsteps')->get($step->id);
        $this->assertSame(3, (int)$persistedStep->validationstatus_id);
    }

    public function testUnRefusSansCommentaireNEnregistreAucunVote(): void
    {
        [$applicationform, $actor, $workflow] = $this->workflowContext();
        $started = $workflow->start($applicationform, $actor);
        $step = TableRegistry::getTableLocator()->get('Applicationvalidationsteps')->find()
            ->where(['validation_workflow_run_id' => $started['run']->id])
            ->firstOrFail();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Un commentaire est obligatoire lors d’un refus.');
        try {
            $workflow->vote($applicationform, $actor, (int)$step->id, false, '', false);
        } finally {
            $this->assertSame(0, TableRegistry::getTableLocator()->get('Validations')->find()
                ->where(['applicationvalidationstep_id' => $step->id])
                ->count());
        }
    }

    /** Vérifie qu'une étape échue est relancée une seule fois par période de vingt-quatre heures. */
    public function testLesRelancesDesEtapesEchuesSontIdempotentesPendantVingtQuatreHeures(): void
    {
        [$applicationform, $actor, $workflow] = $this->workflowContext();
        $started = $workflow->start($applicationform, $actor);
        $step = TableRegistry::getTableLocator()->get('Applicationvalidationsteps')->find()
            ->where(['validation_workflow_run_id' => $started['run']->id])
            ->firstOrFail();
        $step->due_at = DateTime::now()->subHours(1);
        TableRegistry::getTableLocator()->get('Applicationvalidationsteps')->saveOrFail($step);

        $firstRun = $workflow->collectDueReminders();
        $secondRun = $workflow->collectDueReminders();

        $this->assertCount(1, $firstRun);
        $this->assertSame(2, (int)$firstRun[0]['recipients'][0]->id);
        $this->assertSame([], $secondRun);
        $persistedStep = TableRegistry::getTableLocator()->get('Applicationvalidationsteps')->get($step->id);
        $this->assertSame(1, (int)$persistedStep->reminder_count);
        $this->assertNotNull($persistedStep->last_reminded_at);
    }

    /** Vérifie qu'un refus commenté clôt immédiatement le cycle. */
    public function testUnRefusCommenteClotureLeCycleEtTraceLeMotif(): void
    {
        [$applicationform, $actor, $workflow] = $this->workflowContext();
        $started = $workflow->start($applicationform, $actor);
        $step = TableRegistry::getTableLocator()->get('Applicationvalidationsteps')->find()
            ->where(['validation_workflow_run_id' => $started['run']->id])
            ->firstOrFail();

        $result = $workflow->vote($applicationform, $actor, (int)$step->id, false, 'Budget insuffisant.', false);

        $this->assertSame('refusee', $result['state']);
        $this->assertTrue($result['final']);
        $persistedStep = TableRegistry::getTableLocator()->get('Applicationvalidationsteps')->get($step->id);
        $this->assertSame(5, (int)$persistedStep->validationstatus_id);
        $this->assertSame(1, TableRegistry::getTableLocator()->get('Validations')->find()
            ->where([
                'applicationvalidationstep_id' => $step->id,
                'validationstatus_id' => 5,
                'obs' => 'Budget insuffisant.',
            ])
            ->count());
    }

    /** Vérifie que l'acceptation ouvre seulement la séquence suivante. */
    public function testLAcceptationOuvreLaSequenceSuivantePuisClotureAuDernierVote(): void
    {
        $this->prepareSecondSequence();
        [$applicationform, $actor, $workflow] = $this->workflowContext();
        /** @var \App\Model\Entity\User $nextActor */
        $nextActor = TableRegistry::getTableLocator()->get('Users')->get(3);
        $started = $workflow->start($applicationform, $actor);
        $firstStep = TableRegistry::getTableLocator()->get('Applicationvalidationsteps')->find()
            ->where(['validation_workflow_run_id' => $started['run']->id, 'sequence_number' => 1])
            ->firstOrFail();

        $firstVote = $workflow->vote($applicationform, $actor, (int)$firstStep->id, true, 'Accord.', false);

        $this->assertFalse($firstVote['final']);
        $this->assertSame('en_attente', $firstVote['state']);
        $this->assertCount(1, $firstVote['nextRecipients']);
        $this->assertSame(3, (int)$firstVote['nextRecipients'][0]->id);
        $secondStep = TableRegistry::getTableLocator()->get('Applicationvalidationsteps')->find()
            ->where(['validation_workflow_run_id' => $started['run']->id, 'sequence_number' => 2])
            ->firstOrFail();
        $this->assertSame('en_attente', $secondStep->state);
        $this->assertNotNull($secondStep->activated_at);
        $this->assertNotNull($secondStep->due_at);

        $lastVote = $workflow->vote($applicationform, $nextActor, (int)$secondStep->id, true, 'Accord final.', false);

        $this->assertTrue($lastVote['final']);
        $this->assertSame('acceptee', $lastVote['state']);
    }

    /** @return array{\App\Model\Entity\Applicationform, \App\Model\Entity\User, ApplicationformValidationWorkflow} */
    private function workflowContext(): array
    {
        /** @var \App\Model\Entity\Applicationform $applicationform */
        $applicationform = TableRegistry::getTableLocator()->get('Applicationforms')->get(1);
        /** @var \App\Model\Entity\User $actor */
        $actor = TableRegistry::getTableLocator()->get('Users')->get(2);

        return [$applicationform, $actor, new ApplicationformValidationWorkflow()];
    }

    /** Ajoute un second validateur et une séquence postérieure à la première. */
    private function prepareSecondSequence(): void
    {
        $connection = ConnectionManager::get('test');
        $now = '2026-09-18 10:00:00';
        $connection->insert('roles', [
            'id' => 3,
            'base' => 0,
            'code' => 'val-2',
            'name' => 'Second validateur',
            'sort' => 'Second validateur',
            'deleted' => null,
            'created' => $now,
            'modified' => $now,
        ]);
        $connection->insert('users', [
            'id' => 3,
            'username' => 'second-validateur',
            'email' => 'second-validateur@example.test',
            'password' => 'mot-de-passe-de-test',
            'firstname' => 'Second',
            'lastname' => 'Validateur',
            'token' => null,
            'issuperuser' => 0,
            'role_id' => 3,
            'token_expires' => null,
            'deleted' => null,
            'created' => $now,
            'modified' => $now,
        ]);
        $connection->insert('user_departments', [
            'id' => 3,
            'user_id' => 3,
            'department_id' => 1,
            'created' => $now,
            'modified' => $now,
        ]);
        $connection->insert('validationsequences', [
            'id' => 2,
            'department_id' => 1,
            'role_id' => 3,
            'sequence' => 2,
            'reminder_delay_hours' => 12,
            'deleted' => null,
            'created' => $now,
            'modified' => $now,
        ]);
    }
}
