<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ApplicationvalidationstepsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ApplicationvalidationstepsTable Test Case
 */
class ApplicationvalidationstepsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ApplicationvalidationstepsTable
     */
    protected $Applicationvalidationsteps;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Applicationvalidationsteps',
        'app.Applicationforms',
        'app.Roles',
        'app.Validationstatuses',
        'app.Validationsequences',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Applicationvalidationsteps') ? [] : ['className' => ApplicationvalidationstepsTable::class];
        $this->Applicationvalidationsteps = $this->getTableLocator()->get('Applicationvalidationsteps', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Applicationvalidationsteps);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ApplicationvalidationstepsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ApplicationvalidationstepsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
