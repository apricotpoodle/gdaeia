<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CgrStrategiesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CgrStrategiesTable Test Case
 */
class CgrStrategiesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\CgrStrategiesTable
     */
    protected $CgrStrategies;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.CgrStrategies',
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
        $config = $this->getTableLocator()->exists('CgrStrategies') ? [] : ['className' => CgrStrategiesTable::class];
        $this->CgrStrategies = $this->getTableLocator()->get('CgrStrategies', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->CgrStrategies);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\CgrStrategiesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\CgrStrategiesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
