<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PeriodsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\PeriodsTable Test Case
 */
class PeriodsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\PeriodsTable
     */
    protected $Periods;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Periods',
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
        $config = $this->getTableLocator()->exists('Periods') ? [] : ['className' => PeriodsTable::class];
        $this->Periods = $this->getTableLocator()->get('Periods', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Periods);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\PeriodsTable::validationDefault()
     */
    public function testValidationParDefaut(): void
    {
        $entity = $this->Periods->newEntity(['base' => 'invalide', 'code' => str_repeat('a', 17), 'name' => '', 'sort' => '']);
        $this->assertArrayHasKey('base', $entity->getErrors());
        $this->assertArrayHasKey('code', $entity->getErrors());
        $this->assertArrayHasKey('name', $entity->getErrors());
        $this->assertArrayHasKey('sort', $entity->getErrors());
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\PeriodsTable::buildRules()
     */
    public function testReglesIntegrite(): void
    {
        $entity = $this->Periods->newEntity(['base' => false, 'code' => 'Lorem ipsum do', 'name' => 'Nouveau', 'sort' => 'nouveau']);
        $this->assertFalse($this->Periods->save($entity));
        $this->assertArrayHasKey('code', $entity->getErrors());
    }
}
