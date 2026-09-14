<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ProfessionalcategoriesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ProfessionalcategoriesTable Test Case
 */
class ProfessionalcategoriesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ProfessionalcategoriesTable
     */
    protected $Professionalcategories;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Professionalcategories',
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
        $config = $this->getTableLocator()->exists('Professionalcategories') ? [] : ['className' => ProfessionalcategoriesTable::class];
        $this->Professionalcategories = $this->getTableLocator()->get('Professionalcategories', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Professionalcategories);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ProfessionalcategoriesTable::validationDefault()
     */
    public function testValidationParDefaut(): void
    {
        $entity = $this->Professionalcategories->newEntity(['base' => 'invalide', 'code' => str_repeat('a', 17), 'name' => '', 'sort' => '']);
        $this->assertArrayHasKey('base', $entity->getErrors());
        $this->assertArrayHasKey('code', $entity->getErrors());
        $this->assertArrayHasKey('name', $entity->getErrors());
        $this->assertArrayHasKey('sort', $entity->getErrors());
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ProfessionalcategoriesTable::buildRules()
     */
    public function testReglesIntegrite(): void
    {
        $entity = $this->Professionalcategories->newEntity(['base' => false, 'code' => 'Lorem ipsum do', 'name' => 'Nouveau', 'sort' => 'nouveau']);
        $this->assertFalse($this->Professionalcategories->save($entity));
        $this->assertArrayHasKey('code', $entity->getErrors());
    }
}
