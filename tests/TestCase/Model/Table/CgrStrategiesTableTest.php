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
    public function testValidationParDefaut(): void
    {
        $strategy = $this->CgrStrategies->newEntity([
            'code' => str_repeat('a', 33),
            'name' => '',
            'definition_json' => '',
        ]);

        $this->assertArrayHasKey('code', $strategy->getErrors());
        $this->assertArrayHasKey('name', $strategy->getErrors());
        $this->assertArrayHasKey('definition_json', $strategy->getErrors());
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\CgrStrategiesTable::buildRules()
     */
    public function testReglesIntegrite(): void
    {
        $strategy = $this->CgrStrategies->newEntity([
            'code' => 'Lorem ipsum dolor sit amet',
            'name' => 'Strategie alternative',
            'definition_json' => '[{"type":"AXE"}]',
        ]);

        $this->assertFalse($this->CgrStrategies->save($strategy));
        $this->assertArrayHasKey('code', $strategy->getErrors());
    }
}
