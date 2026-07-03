<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ValidationVisasTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ValidationVisasTable Test Case
 */
class ValidationVisasTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ValidationVisasTable
     */
    protected $ValidationVisas;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.ValidationVisas',
        'app.Applicationforms',
        'app.Roles',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('ValidationVisas') ? [] : ['className' => ValidationVisasTable::class];
        $this->ValidationVisas = $this->getTableLocator()->get('ValidationVisas', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->ValidationVisas);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ValidationVisasTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ValidationVisasTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
