<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\BudgetfeaturesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\BudgetfeaturesTable Test Case
 */
class BudgetfeaturesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\BudgetfeaturesTable
     */
    protected $Budgetfeatures;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Budgetfeatures',
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
        $config = $this->getTableLocator()->exists('Budgetfeatures') ? [] : ['className' => BudgetfeaturesTable::class];
        $this->Budgetfeatures = $this->getTableLocator()->get('Budgetfeatures', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Budgetfeatures);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\BudgetfeaturesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\BudgetfeaturesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
