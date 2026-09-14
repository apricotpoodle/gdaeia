<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ApplicationvalidationstepsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ApplicationvalidationstepsTable Test Case
 */
class ApplicationvalidationstepsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ApplicationvalidationstepsTable
     */
    protected $Applicationvalidationsteps;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Applicationvalidationsteps',
        'app.Applicationforms',
        'app.Roles',
        'app.Validationstatuses',
        'app.Validationsequences',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Applicationvalidationsteps') ? [] : ['className' => ApplicationvalidationstepsTable::class];
        $this->Applicationvalidationsteps = $this->getTableLocator()->get('Applicationvalidationsteps', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Applicationvalidationsteps);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ApplicationvalidationstepsTable::validationDefault()
     */
    public function testValidationParDefaut(): void
    {
        $step = $this->Applicationvalidationsteps->newEntity([
            'applicationform_id' => 1,
            'role_id' => -1,
            'validationstatus_id' => 'invalide',
            'validationsequence_id' => 1,
            'comment' => str_repeat('a', 101),
        ]);

        $this->assertArrayHasKey('role_id', $step->getErrors());
        $this->assertArrayHasKey('validationstatus_id', $step->getErrors());
        $this->assertArrayHasKey('comment', $step->getErrors());
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ApplicationvalidationstepsTable::buildRules()
     */
    public function testReglesIntegrite(): void
    {
        $step = $this->Applicationvalidationsteps->newEntity([
            'applicationform_id' => 1,
            'role_id' => 1,
            'validationstatus_id' => 99999,
            'validationsequence_id' => 1,
        ]);

        $this->assertFalse($this->Applicationvalidationsteps->save($step));
        $this->assertArrayHasKey('validationstatus_id', $step->getErrors());
    }
}
