<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Service\Workflow\ApplicationformValidationWorkflow;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/** Le verrou du circuit appartient au modèle et survit à tous les états du cycle. */
class ApplicationformCircuitLockTest extends TestCase
{
    protected array $fixtures = [
        'app.Applicationforms', 'app.Users', 'app.Departments', 'app.Contracttypes',
        'app.Roles', 'app.Validationsequences', 'app.Applicationvalidationsteps',
        'app.ValidationWorkflowRuns', 'app.Validations', 'app.Periods',
    ];

    #[DataProvider('cyclesEtChamps')]
    public function testLeModeleRefuseToutChangementDeCircuitMemeSansRegles(string $state, string $field): void
    {
        $this->createRun($state);
        $table = $this->getTableLocator()->get('Applicationforms');
        $form = $table->get(1);
        $form->set($field, 2);

        $this->assertFalse($table->save($form, ['checkRules' => false]));
        $this->assertArrayHasKey($field, $form->getErrors());
        $this->assertSame(1, (int)$table->get(1)->get($field));
    }

    /** @return array<string, array{string, string}> */
    public static function cyclesEtChamps(): array
    {
        return [
            'département en attente' => ['en_attente', 'department_id'],
            'contrat en attente' => ['en_attente', 'contracttype_id'],
            'département accepté' => ['acceptee', 'department_id'],
            'contrat accepté' => ['acceptee', 'contracttype_id'],
            'département refusé' => ['refusee', 'department_id'],
            'contrat refusé' => ['refusee', 'contracttype_id'],
            'département annulé' => ['annulee', 'department_id'],
            'contrat annulé' => ['annulee', 'contracttype_id'],
        ];
    }

    public function testLaRemiseAZeroLibereLeContratEtLeDepartement(): void
    {
        $runId = $this->createRun('acceptee');
        ConnectionManager::get('test')->insert('applicationvalidationsteps', [
            'applicationform_id' => 1,
            'validation_workflow_run_id' => $runId,
            'validationsequence_id' => 1,
            'role_id' => 2,
            'sequence_number' => 2,
            'state' => 'a_venir',
            'reminder_count' => 0,
        ]);
        ConnectionManager::get('test')->insert('contracttypes', [
            'id' => 2, 'base' => 1, 'code' => 'AUTRE', 'name' => 'Autre contrat', 'sort' => 'Autre contrat',
            'created' => '2026-11-06 10:00:00', 'modified' => '2026-11-06 10:00:00',
        ]);
        $table = $this->getTableLocator()->get('Applicationforms');
        $form = $table->get(1);
        (new ApplicationformValidationWorkflow())->reset($form);
        $form->set('department_id', 2);
        $form->set('contracttype_id', 2);

        $this->assertNotFalse($table->save($form, ['checkRules' => false]));
        $this->assertSame(2, (int)$table->get(1)->department_id);
        $this->assertSame(2, (int)$table->get(1)->contracttype_id);
    }

    public function testUneSauvegardeDuMemeCircuitRestePossible(): void
    {
        $this->createRun('acceptee');
        $table = $this->getTableLocator()->get('Applicationforms');
        $form = $table->get(1);
        $form->set('department_id', '1');
        $form->set('contracttype_id', '1');
        $form->set('jobtitle', 'Poste corrigé');

        $this->assertNotFalse($table->save($form, ['checkRules' => false]));
        $this->assertSame('Poste corrigé', $table->get(1)->jobtitle);
    }

    private function createRun(string $state): int
    {
        $connection = ConnectionManager::get('test');
        $connection->insert('validation_workflow_runs', [
            'applicationform_id' => 1, 'started_by_user_id' => 1, 'state' => $state,
            'started_at' => '2026-11-06 10:00:00', 'created' => '2026-11-06 10:00:00',
            'modified' => '2026-11-06 10:00:00',
        ]);
        $runId = (int)$connection->execute(
            'SELECT id FROM validation_workflow_runs WHERE applicationform_id = 1',
        )->fetchColumn(0);
        $connection->insert('applicationvalidationsteps', [
            'applicationform_id' => 1,
            'validation_workflow_run_id' => $runId,
            'validationsequence_id' => 1,
            'role_id' => 1,
            'sequence_number' => 1,
            'state' => $state,
            'activated_at' => '2026-11-06 10:00:00',
            'reminder_count' => 0,
        ]);

        return $runId;
    }
}
