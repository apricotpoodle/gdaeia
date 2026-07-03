<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ValidationstatusesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ValidationstatusesTable Test Case
 */
class ValidationstatusesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ValidationstatusesTable
     */
    protected $Validationstatuses;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Validationstatuses',
        'app.Applicationformstatuses',
        'app.Applicationvalidationsteps',
        'app.Currentvalidationroles',
        'app.Validations',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Validationstatuses') ? [] : ['className' => ValidationstatusesTable::class];
        $this->Validationstatuses = $this->getTableLocator()->get('Validationstatuses', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Validationstatuses);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ValidationstatusesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
