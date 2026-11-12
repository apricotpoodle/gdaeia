<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\User;
use App\Model\Table\UsersTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\UsersTable Test Case
 */
class UsersTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\UsersTable
     */
    protected $Users;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Roles',
        'app.Applicationforms',
        'app.UserDepartments',
        'app.Departments',
        'app.Validations',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Users') ? [] : ['className' => UsersTable::class];
        $this->Users = $this->getTableLocator()->get('Users', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Users);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\UsersTable::validationDefault()
     */
    public function testValidationParDefaut(): void
    {
        $user = $this->Users->newEntity([
            'email' => 'adresse-invalide',
            'password' => '',
            'issuperuser' => 'invalide',
            'role_id' => 'invalide',
        ]);

        $this->assertArrayHasKey('email', $user->getErrors());
        $this->assertArrayHasKey('password', $user->getErrors());
        $this->assertArrayHasKey('issuperuser', $user->getErrors());
        $this->assertArrayHasKey('role_id', $user->getErrors());
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\UsersTable::buildRules()
     */
    public function testReglesIntegrite(): void
    {
        $user = $this->Users->newEntity([
            'username' => 'Lorem ipsum dolor sit amet',
            'email' => 'nouvel.utilisateur@example.test',
            'password' => 'mot-de-passe-sur',
            'issuperuser' => false,
            'role_id' => 1,
        ]);

        $this->assertFalse($this->Users->save($user));
        $this->assertArrayHasKey('username', $user->getErrors());
    }

    public function testLeFinderAssistantsForDepartmentExclutLesComptesIneligibles(): void
    {
        $connection = $this->Users->getConnection();
        $departments = $this->getTableLocator()->get('Departments');
        $departments->updateAll(['deleted' => null], ['id IN' => [1, 2]]);
        $connection->insert('departments', [
            'id' => 3,
            'parent_id' => null,
            'lft' => 5,
            'rght' => 6,
            'level' => 1,
            'base' => 1,
            'code' => 'AUTRE-SERVICE',
            'name' => 'Autre service',
            'sort' => 'Autre service',
            'department_type_id' => 1,
            'deleted' => null,
        ]);
        $connection->update('roles', ['deleted' => null], ['id' => User::ROLE_ADMIN]);
        $connection->insert('roles', [
            'id' => User::ROLE_DEMANDEUR,
            'base' => 1,
            'code' => 'DEMANDEUR',
            'name' => 'Demandeur',
            'sort' => 'Demandeur',
            'deleted' => null,
            'created' => '2026-07-03 09:01:32',
            'modified' => '2026-07-03 09:01:32',
        ]);

        foreach ([3, 4, 5, 6] as $id) {
            $connection->insert('users', [
                'id' => $id,
                'username' => 'assistante-' . $id,
                'email' => 'assistante-' . $id . '@example.test',
                'password' => 'mot-de-passe',
                'firstname' => 'Assistante',
                'lastname' => (string)$id,
                'issuperuser' => 0,
                'role_id' => $id === 5 ? User::ROLE_ADMIN : User::ROLE_DEMANDEUR,
                'deleted' => $id === 6 ? '2026-07-03 09:01:33' : null,
                'created' => '2026-07-03 09:01:33',
                'modified' => '2026-07-03 09:01:33',
            ]);
        }

        $userDepartments = $this->getTableLocator()->get('UserDepartments');
        $userDepartments->saveManyOrFail($userDepartments->newEntities([
            ['user_id' => 2, 'department_id' => 2],
            ['user_id' => 3, 'department_id' => 2],
            ['user_id' => 4, 'department_id' => 3],
            ['user_id' => 5, 'department_id' => 2],
            ['user_id' => 6, 'department_id' => 2],
        ]));

        $assistants = $this->Users->find('assistantsForDepartment', departmentId: 2)
            ->find('list', keyField: 'id', valueField: 'display_name')
            ->toArray();

        $this->assertSame([3], array_map('intval', array_keys($assistants)));
        $this->assertSame('Assistante 3', $assistants[3]);
    }

    public function testLeFinderManageableToRetourneLesUtilisateursLocauxEtExclutLesAncetres(): void
    {
        $connection = $this->Users->getConnection();
        $departments = $this->getTableLocator()->get('Departments');
        $departments->updateAll(['deleted' => null], ['id IN' => [1, 2]]);
        $connection->insert('departments', [
            'id' => 3,
            'parent_id' => null,
            'lft' => 5,
            'rght' => 6,
            'level' => 1,
            'base' => 1,
            'code' => 'BRANCHE-INDEPENDANTE',
            'name' => 'Branche indépendante',
            'sort' => 'Branche indépendante',
            'department_type_id' => 1,
            'deleted' => null,
        ]);
        $connection->insert('departments', [
            'id' => 4,
            'parent_id' => null,
            'lft' => 7,
            'rght' => 8,
            'level' => 1,
            'base' => 1,
            'code' => 'BRANCHE-SECONDE',
            'name' => 'Branche seconde',
            'sort' => 'Branche seconde',
            'department_type_id' => 1,
            'deleted' => null,
        ]);
        foreach ([3, 4, 5] as $id) {
            $connection->insert('users', [
                'id' => $id,
                'username' => 'utilisateur-' . $id,
                'email' => 'utilisateur-' . $id . '@example.test',
                'password' => 'mot-de-passe',
                'firstname' => 'Utilisateur',
                'lastname' => (string)$id,
                'issuperuser' => 0,
                'role_id' => 1,
                'deleted' => null,
                'created' => '2026-07-03 09:01:33',
                'modified' => '2026-07-03 09:01:33',
            ]);
        }

        $userDepartments = $this->getTableLocator()->get('UserDepartments');
        $userDepartments->saveManyOrFail($userDepartments->newEntities([
            ['user_id' => 2, 'department_id' => 2],
            ['user_id' => 2, 'department_id' => 4],
            ['user_id' => 3, 'department_id' => 2],
            ['user_id' => 3, 'department_id' => 4],
            ['user_id' => 4, 'department_id' => 1],
            ['user_id' => 4, 'department_id' => 2],
            ['user_id' => 5, 'department_id' => 3],
        ]));

        $manageable = $this->Users->find('manageableTo', user: new User([
            'id' => 2,
            'issuperuser' => false,
        ]))->all()->toList();

        $this->assertSame([2, 3], array_map(
            static fn(User $user): int => (int)$user->get('id'),
            $manageable,
        ));
    }

    public function testLeFinderManageableToNeRetourneRienSansPerimetre(): void
    {
        $manageable = $this->Users->find('manageableTo', user: new User([
            'id' => 2,
            'issuperuser' => false,
        ]))->all()->toList();

        $this->assertSame([], $manageable);
    }

    public function testLeFinderManageableToConserveLePerimetreGlobalDuSuperuser(): void
    {
        $query = $this->Users->find();
        $this->assertSame(
            $query,
            $this->Users->findManageableTo($query, new User([
                'id' => 1,
                'issuperuser' => true,
            ])),
        );
    }
}
