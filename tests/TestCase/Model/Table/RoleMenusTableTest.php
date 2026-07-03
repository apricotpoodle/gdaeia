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
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\RoleMenusTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
