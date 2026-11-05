<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\Department;
use App\Model\Entity\User;
use App\Model\Table\DepartmentsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\DepartmentsTable Test Case
 */
class DepartmentsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\DepartmentsTable
     */
    protected $Departments;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Departments',
        'app.Roles',
        'app.Users',
        'app.UserDepartments',
        'app.CgrCodes',
        'app.CgrStrategies',
        'app.Applicationforms',
        'app.Permissions',
        'app.PermissionScopes',
        'app.RolePermissions',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Departments') ? [] : ['className' => DepartmentsTable::class];
        $this->Departments = $this->getTableLocator()->get('Departments', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Departments);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\DepartmentsTable::validationDefault()
     */
    public function testValidationParDefaut(): void
    {
        $this->assertTrue($this->Departments->behaviors()->has('Tree'));
        $this->assertTrue($this->Departments->associations()->has('ParentDepartments'));
        $this->assertTrue($this->Departments->associations()->has('ChildDepartments'));
        $this->assertTrue($this->Departments->associations()->has('CgrStrategies'));
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\DepartmentsTable::buildRules()
     */
    public function testReglesIntegrite(): void
    {
        $query = $this->Departments->find();

        $this->assertSame(
            $query,
            $this->Departments->findVisibleTo($query, new User(['issuperuser' => true])),
        );
    }

    public function testLeFinderVisibleToLimiteUnAdministrateurASesAssociationsExplicites(): void
    {
        $departments = $this->getTableLocator()->get('Departments');
        $departments->updateAll(['deleted' => null], ['id IN' => [1, 2]]);
        $userDepartments = $this->getTableLocator()->get('UserDepartments');
        $userDepartments->saveOrFail($userDepartments->newEntity([
            'user_id' => 2,
            'department_id' => 2,
        ]));

        $visible = $departments->find('visibleTo', user: new User([
            'id' => 2,
            'issuperuser' => false,
            'role_id' => User::ROLE_ADMIN,
        ]))->all()->toList();

        $this->assertSame([2], array_map(
            static fn(Department $department): int => (int)$department->get('id'),
            $visible,
        ));
    }

    public function testLeFinderVisibleToNeRetourneAucuneRacineSansAssociation(): void
    {
        $departments = $this->getTableLocator()->get('Departments');
        $departments->updateAll(['deleted' => null], ['id IN' => [1, 2]]);

        $visible = $departments->find('visibleTo', user: new User([
            'id' => 2,
            'issuperuser' => false,
            'role_id' => User::ROLE_ADMIN,
        ]))->all()->toList();

        $this->assertSame([], $visible);
    }

    public function testLeFinderDesAncetresStrictsIgnoreLesDepartementsInactifs(): void
    {
        $departments = $this->getTableLocator()->get('Departments');
        $connection = $departments->getConnection();
        $connection->insert('departments', [
            'id' => 3,
            'parent_id' => null,
            'lft' => 5,
            'rght' => 8,
            'level' => 1,
            'base' => 1,
            'code' => 'BRANCHE-TEST',
            'name' => 'Branche de test',
            'sort' => 'Branche de test',
            'department_type_id' => 1,
            'deleted' => null,
        ]);
        $connection->insert('departments', [
            'id' => 4,
            'parent_id' => 3,
            'lft' => 6,
            'rght' => 7,
            'level' => 2,
            'base' => 1,
            'code' => 'SERVICE-BRANCHE-TEST',
            'name' => 'Service de branche de test',
            'sort' => 'Service de branche de test',
            'department_type_id' => 1,
            'deleted' => null,
        ]);
        $departments->updateAll(['deleted' => null], ['id IN' => [2, 3, 4]]);

        $ancestors = $departments->find('strictAncestorsOf', roots: [
            ['lft' => 2, 'rght' => 3],
            ['lft' => 6, 'rght' => 7],
        ])->all()->toList();

        $this->assertSame([3], array_map(
            static fn(Department $department): int => (int)$department->get('id'),
            $ancestors,
        ));
    }

    public function testLeFinderDesAncetresStrictsNInclutPasLaRacineElleMeme(): void
    {
        $departments = $this->getTableLocator()->get('Departments');
        $departments->updateAll(['deleted' => null], ['id IN' => [1, 2, 3, 4]]);

        $ancestors = $departments->find('strictAncestorsOf', roots: [
            ['lft' => 1, 'rght' => 4],
        ])->all()->toList();

        $this->assertSame([], $ancestors);
    }

    public function testLeFinderDesAncetresStrictsRetourneUnResultatVideSansRacine(): void
    {
        $ancestors = $this->Departments->find('strictAncestorsOf', roots: [])->all()->toList();

        $this->assertSame([], $ancestors);
    }
}
