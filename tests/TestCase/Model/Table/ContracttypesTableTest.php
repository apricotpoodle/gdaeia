<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ContracttypesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ContracttypesTable Test Case
 */
class ContracttypesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ContracttypesTable
     */
    protected $Contracttypes;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Contracttypes',
        'app.Applicationforms',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Contracttypes') ? [] : ['className' => ContracttypesTable::class];
        $this->Contracttypes = $this->getTableLocator()->get('Contracttypes', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Contracttypes);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ContracttypesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ContracttypesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
