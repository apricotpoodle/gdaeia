<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\RoleMenusTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\RoleMenusTable Test Case
 */
class RoleMenusTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\RoleMenusTable
     */
    protected $RoleMenus;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.RoleMenus',
        'app.Roles',
        'app.Menus',
        'app.Departments',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('RoleMenus') ? [] : ['className' => RoleMenusTable::class];
        $this->RoleMenus = $this->getTableLocator()->get('RoleMenus', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->RoleMenus);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\RoleMenusTable::validationDefault()
     */
    public function testValidationParDefaut(): void
    {
        $roleMenu = $this->RoleMenus->newEntity([
            'role_id' => 'invalide',
            'menu_id' => 'invalide',
            'department_id' => -1,
        ]);

        $this->assertArrayHasKey('role_id', $roleMenu->getErrors());
        $this->assertArrayHasKey('menu_id', $roleMenu->getErrors());
        $this->assertArrayHasKey('department_id', $roleMenu->getErrors());
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\RoleMenusTable::buildRules()
     */
    public function testReglesIntegrite(): void
    {
        $roleMenu = $this->RoleMenus->newEntity([
            'role_id' => 1,
            'menu_id' => 99999,
        ]);

        $this->assertFalse($this->RoleMenus->save($roleMenu));
        $this->assertArrayHasKey('menu_id', $roleMenu->getErrors());
    }
}
