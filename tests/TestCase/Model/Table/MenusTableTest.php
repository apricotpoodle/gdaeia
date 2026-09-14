<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MenusTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MenusTable Test Case
 */
class MenusTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\MenusTable
     */
    protected $Menus;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Menus',
        'app.RoleMenus',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Menus') ? [] : ['className' => MenusTable::class];
        $this->Menus = $this->getTableLocator()->get('Menus', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Menus);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\MenusTable::validationDefault()
     */
    public function testValidationParDefaut(): void
    {
        $menu = $this->Menus->newEntity([
            'parent_id' => 'invalide',
            'level' => 'invalide',
            'active' => 'invalide',
            'disabled' => 'invalide',
        ]);

        $this->assertArrayHasKey('parent_id', $menu->getErrors());
        $this->assertArrayHasKey('level', $menu->getErrors());
        $this->assertArrayHasKey('active', $menu->getErrors());
        $this->assertArrayHasKey('disabled', $menu->getErrors());
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\MenusTable::buildRules()
     */
    public function testReglesIntegrite(): void
    {
        $menu = $this->Menus->newEntity([
            'parent_id' => 99999,
            'name' => 'Menu enfant',
            'active' => true,
        ]);

        $this->assertFalse($this->Menus->save($menu));
        $this->assertArrayHasKey('parent_id', $menu->getErrors());
    }
}
