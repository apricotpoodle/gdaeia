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
    public function testValidationParDefaut(): void
    {
        $sequence = $this->Validationsequences->newEntity([
            'department_id' => -1,
            'name' => '',
            'role_id' => -1,
            'sequence' => 'invalide',
        ]);

        $this->assertArrayHasKey('department_id', $sequence->getErrors());
        $this->assertArrayHasKey('name', $sequence->getErrors());
        $this->assertArrayHasKey('role_id', $sequence->getErrors());
        $this->assertArrayHasKey('sequence', $sequence->getErrors());
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ValidationsequencesTable::buildRules()
     */
    public function testReglesIntegrite(): void
    {
        $sequence = $this->Validationsequences->newEntity([
            'department_id' => 1,
            'name' => 'Validation RH',
            'role_id' => 99999,
            'sequence' => 2,
        ]);

        $this->assertFalse($this->Validationsequences->save($sequence));
        $this->assertArrayHasKey('role_id', $sequence->getErrors());
    }
}
