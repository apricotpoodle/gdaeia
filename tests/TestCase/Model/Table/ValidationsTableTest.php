<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ValidationsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ValidationsTable Test Case
 */
class ValidationsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ValidationsTable
     */
    protected $Validations;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Validations',
        'app.Applicationforms',
        'app.Users',
        'app.Roles',
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
        $config = $this->getTableLocator()->exists('Validations') ? [] : ['className' => ValidationsTable::class];
        $this->Validations = $this->getTableLocator()->get('Validations', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Validations);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ValidationsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ValidationsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
