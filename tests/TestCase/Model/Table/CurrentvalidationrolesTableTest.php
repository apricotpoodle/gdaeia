<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CurrentvalidationrolesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CurrentvalidationrolesTable Test Case
 */
class CurrentvalidationrolesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\CurrentvalidationrolesTable
     */
    protected $Currentvalidationroles;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Applicationforms',
        'app.Departments',
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
        $config = $this->getTableLocator()->exists('Currentvalidationroles') ? [] : ['className' => CurrentvalidationrolesTable::class];
        $this->Currentvalidationroles = $this->getTableLocator()->get('Currentvalidationroles', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Currentvalidationroles);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\CurrentvalidationrolesTable::validationDefault()
     */
    public function testValidationParDefaut(): void
    {
        $entity = $this->Currentvalidationroles->newEntity(['applicationform_id' => -1, 'department_id' => 'invalide', 'validator_role_id' => -1, 'validation_sequence' => 'invalide', 'en_cours' => 'invalide']);
        $this->assertArrayHasKey('applicationform_id', $entity->getErrors());
        $this->assertArrayHasKey('department_id', $entity->getErrors());
        $this->assertArrayHasKey('validator_role_id', $entity->getErrors());
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\CurrentvalidationrolesTable::buildRules()
     */
    public function testReglesIntegrite(): void
    {
        $this->assertTrue($this->Currentvalidationroles->associations()->has('Applicationforms'));
        $this->assertTrue($this->Currentvalidationroles->associations()->has('Departments'));
    }
}
