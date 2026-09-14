<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CgrCodesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CgrCodesTable Test Case
 */
class CgrCodesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\CgrCodesTable
     */
    protected $CgrCodes;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.CgrCodes',
        'app.Departments',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('CgrCodes') ? [] : ['className' => CgrCodesTable::class];
        $this->CgrCodes = $this->getTableLocator()->get('CgrCodes', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->CgrCodes);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\CgrCodesTable::validationDefault()
     */
    public function testValidationParDefaut(): void
    {
        $code = $this->CgrCodes->newEntity([
            'department_id' => -1,
            'type' => '',
            'code' => str_repeat('a', 17),
            'label' => '',
            'active' => 'invalide',
            'is_system' => 'invalide',
        ]);

        $this->assertArrayHasKey('department_id', $code->getErrors());
        $this->assertArrayHasKey('type', $code->getErrors());
        $this->assertArrayHasKey('code', $code->getErrors());
        $this->assertArrayHasKey('label', $code->getErrors());
        $this->assertArrayHasKey('active', $code->getErrors());
        $this->assertArrayHasKey('is_system', $code->getErrors());
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\CgrCodesTable::buildRules()
     */
    public function testReglesIntegrite(): void
    {
        $code = $this->CgrCodes->newEntity([
            'department_id' => 99999,
            'type' => 'AXE',
            'code' => 'A1',
            'label' => 'Axe un',
            'active' => true,
            'is_system' => false,
        ]);

        $this->assertFalse($this->CgrCodes->save($code));
        $this->assertArrayHasKey('department_id', $code->getErrors());
    }
}
