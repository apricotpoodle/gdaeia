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

    /** Le libellé technique peut rester vide, mais une étape est numérotée à partir de 1. */
    public function testLeLibellePeutEtreVideEtLaSequenceDoitEtrePositive(): void
    {
        $sequence = $this->Validationsequences->newEntity([
            'department_id' => 1,
            'name' => '',
            'role_id' => 1,
            'sequence' => 0,
        ]);

        $this->assertArrayNotHasKey('name', $sequence->getErrors());
        $this->assertArrayHasKey('sequence', $sequence->getErrors());
    }

    /** Chaque département configuré doit commencer à 1 et ne comporter aucun trou. */
    public function testLesSequencesActivesDoiventEtreContinues(): void
    {
        $this->Validationsequences->updateAll(['deleted' => null, 'sequence' => 1], ['id' => 1]);

        $this->assertTrue($this->Validationsequences->hasContiguousSequencesForDepartments([1]));

        $this->Validationsequences->updateAll(['sequence' => 2], ['id' => 1]);

        $this->assertFalse($this->Validationsequences->hasContiguousSequencesForDepartments([1]));
    }
}
