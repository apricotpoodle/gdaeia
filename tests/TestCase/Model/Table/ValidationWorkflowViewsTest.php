<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

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
    ];

    private TableLocator $tables;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tables = $this->getTableLocator();
    }

    /**
     * Le visa initial passe à l'étape suivante puis clôture le workflow accepté.
     *
     * @return void
     */
    public function testLeWorkflowCompletMetAJourLesVuesSql(): void
    {
        $connection = ConnectionManager::get('test');
        $connection->update('applicationforms', ['deleted' => null], ['id' => 1]);
        $connection->update('users', [
            'firstname' => 'Alice',
            'lastname' => 'Validatrice',
        ], ['id' => 1]);
        $connection->update('roles', ['name' => 'Responsable'], ['id' => 1]);
        $connection->update('validationsequences', [
            'name' => 'Visa responsable',
            'role_id' => 1,
            'sequence' => 1,
        ], ['id' => 1]);
        $connection->insert('roles', [
            'id' => 2,
            'base' => true,
            'code' => 'DIRECTION',
            'name' => 'Direction',
            'sort' => '2',
        ]);
        $connection->insert('validationsequences', [
            'id' => 2,
            'department_id' => 1,
            'name' => 'Visa direction',
            'role_id' => 2,
            'sequence' => 2,
        ]);
        $connection->update('validations', [
            'validationstatus_id' => 2,
            'deleted' => null,
        ], ['id' => 1]);

        $this->assertSame(2, (int)$this->workflowStatus()->validationstatus_id);
        $this->assertSame(1, (int)$this->currentRole()->validator_role_id);
        $this->assertSame('', $this->visas()->firstOrFail()->op_name);

        $connection->update('validations', ['validationstatus_id' => 3], ['id' => 1]);

        $this->assertSame(2, (int)$this->currentRole()->validator_role_id);
        $this->assertSame('Alice Validatrice', $this->visas()->firstOrFail()->op_name);

        $connection->insert('validations', [
            'id' => 2,
            'applicationform_id' => 1,
            'user_id' => 1,
            'role_id' => 2,
            'validated' => '2026-09-14 10:00:00',
            'validationstatus_id' => 4,
            'obs' => 'Accord final',
        ]);

        $status = $this->workflowStatus();
        $this->assertSame(4, (int)$status->validationstatus_id);
        $this->assertTrue((bool)$status->accepted);
        $this->assertFalse((bool)$status->rejected);
        $this->assertNull($this->currentRole());
        $this->assertCount(2, $this->visas()->all()->toList());
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
