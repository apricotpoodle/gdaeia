<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CgrCodesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CgrCodesTable Test Case
 */
class CgrCodesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\CgrCodesTable
     */
    protected $CgrCodes;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.CgrCodes',
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
        $config = $this->getTableLocator()->exists('CgrCodes') ? [] : ['className' => CgrCodesTable::class];
        $this->CgrCodes = $this->getTableLocator()->get('CgrCodes', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->CgrCodes);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\CgrCodesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\CgrCodesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
