<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ApplicationformstatusesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ApplicationformstatusesTable Test Case
 */
class ApplicationformstatusesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ApplicationformstatusesTable
     */
    protected $Applicationformstatuses;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Applicationformstatuses',
        'app.Applicationforms',
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
        $config = $this->getTableLocator()->exists('Applicationformstatuses') ? [] : ['className' => ApplicationformstatusesTable::class];
        $this->Applicationformstatuses = $this->getTableLocator()->get('Applicationformstatuses', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Applicationformstatuses);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ApplicationformstatusesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ApplicationformstatusesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
