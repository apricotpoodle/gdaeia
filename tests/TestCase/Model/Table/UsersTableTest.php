<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

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
}
