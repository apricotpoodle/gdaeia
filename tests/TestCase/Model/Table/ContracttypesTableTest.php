<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ContracttypesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ContracttypesTable Test Case
 */
class ContracttypesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ContracttypesTable
     */
    protected $Contracttypes;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Contracttypes',
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
        $config = $this->getTableLocator()->exists('Contracttypes') ? [] : ['className' => ContracttypesTable::class];
        $this->Contracttypes = $this->getTableLocator()->get('Contracttypes', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Contracttypes);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ContracttypesTable::validationDefault()
     */
    public function testValidationParDefaut(): void
    {
        $entity = $this->Contracttypes->newEntity(['base' => 'invalide', 'code' => str_repeat('a', 17), 'name' => '', 'sort' => '']);
        $this->assertArrayHasKey('base', $entity->getErrors());
        $this->assertArrayHasKey('code', $entity->getErrors());
        $this->assertArrayHasKey('name', $entity->getErrors());
        $this->assertArrayHasKey('sort', $entity->getErrors());
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ContracttypesTable::buildRules()
     */
    public function testReglesIntegrite(): void
    {
        $entity = $this->Contracttypes->newEntity(['base' => false, 'code' => 'Lorem ipsum do', 'name' => 'Nouveau', 'sort' => 'nouveau']);
        $this->assertFalse($this->Contracttypes->save($entity));
        $this->assertArrayHasKey('code', $entity->getErrors());
    }
}
