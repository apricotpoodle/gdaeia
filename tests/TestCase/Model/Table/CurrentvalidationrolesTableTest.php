<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CurrentvalidationrolesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CurrentvalidationrolesTable Test Case
 */
class CurrentvalidationrolesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\CurrentvalidationrolesTable
     */
    protected $Currentvalidationroles;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Currentvalidationroles',
        'app.Applicationforms',
        'app.Departments',
        'app.Validationstatuses',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Currentvalidationroles') ? [] : ['className' => CurrentvalidationrolesTable::class];
        $this->Currentvalidationroles = $this->getTableLocator()->get('Currentvalidationroles', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Currentvalidationroles);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\CurrentvalidationrolesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\CurrentvalidationrolesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
