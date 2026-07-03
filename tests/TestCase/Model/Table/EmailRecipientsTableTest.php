<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\EmailRecipientsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\EmailRecipientsTable Test Case
 */
class EmailRecipientsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\EmailRecipientsTable
     */
    protected $EmailRecipients;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.EmailRecipients',
        'app.EmailLogs',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('EmailRecipients') ? [] : ['className' => EmailRecipientsTable::class];
        $this->EmailRecipients = $this->getTableLocator()->get('EmailRecipients', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->EmailRecipients);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\EmailRecipientsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\EmailRecipientsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
