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
        'app.Applicationvalidationsteps',
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
    public function testValidationParDefaut(): void
    {
        $status = $this->Validationstatuses->newEntity([
            'code' => '',
            'name' => str_repeat('a', 101),
        ]);

        $this->assertArrayHasKey('code', $status->getErrors());
        $this->assertArrayHasKey('name', $status->getErrors());
    }
}
