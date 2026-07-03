<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\EmailLogsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\EmailLogsTable Test Case
 */
class EmailLogsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\EmailLogsTable
     */
    protected $EmailLogs;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.EmailLogs',
        'app.EmailRecipients',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('EmailLogs') ? [] : ['className' => EmailLogsTable::class];
        $this->EmailLogs = $this->getTableLocator()->get('EmailLogs', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->EmailLogs);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\EmailLogsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
