<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ValidationsequencesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ValidationsequencesTable Test Case
 */
class ValidationsequencesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ValidationsequencesTable
     */
    protected $Validationsequences;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Validationsequences',
        'app.Departments',
        'app.Roles',
        'app.Applicationvalidationsteps',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Validationsequences') ? [] : ['className' => ValidationsequencesTable::class];
        $this->Validationsequences = $this->getTableLocator()->get('Validationsequences', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Validationsequences);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ValidationsequencesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ValidationsequencesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
