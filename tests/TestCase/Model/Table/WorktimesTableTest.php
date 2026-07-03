<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\WorktimesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\WorktimesTable Test Case
 */
class WorktimesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\WorktimesTable
     */
    protected $Worktimes;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Worktimes',
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
        $config = $this->getTableLocator()->exists('Worktimes') ? [] : ['className' => WorktimesTable::class];
        $this->Worktimes = $this->getTableLocator()->get('Worktimes', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Worktimes);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\WorktimesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\WorktimesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
