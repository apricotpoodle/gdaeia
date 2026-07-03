<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UrdsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\UrdsTable Test Case
 */
class UrdsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\UrdsTable
     */
    protected $Urds;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Urds',
        'app.Users',
        'app.Roles',
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
        $config = $this->getTableLocator()->exists('Urds') ? [] : ['className' => UrdsTable::class];
        $this->Urds = $this->getTableLocator()->get('Urds', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Urds);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\UrdsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\UrdsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
