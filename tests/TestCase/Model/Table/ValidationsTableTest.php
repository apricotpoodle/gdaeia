<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ValidationsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ValidationsTable Test Case
 */
class ValidationsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ValidationsTable
     */
    protected $Validations;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Validations',
        'app.Applicationforms',
        'app.Users',
        'app.Roles',
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
        $config = $this->getTableLocator()->exists('Validations') ? [] : ['className' => ValidationsTable::class];
        $this->Validations = $this->getTableLocator()->get('Validations', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Validations);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ValidationsTable::validationDefault()
     */
    public function testValidationParDefaut(): void
    {
        $validation = $this->Validations->newEntity([
            'applicationform_id' => 1,
            'user_id' => 'invalide',
            'role_id' => 1,
            'validationstatus_id' => 'invalide',
            'obs' => str_repeat('a', 256),
        ]);

        $this->assertArrayHasKey('user_id', $validation->getErrors());
        $this->assertArrayHasKey('validationstatus_id', $validation->getErrors());
        $this->assertArrayHasKey('obs', $validation->getErrors());
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ValidationsTable::buildRules()
     */
    public function testReglesIntegrite(): void
    {
        $validation = $this->Validations->newEntity([
            'applicationform_id' => 99999,
            'user_id' => 1,
            'role_id' => 1,
            'validationstatus_id' => 1,
        ]);

        $this->assertFalse($this->Validations->save($validation));
        $this->assertArrayHasKey('applicationform_id', $validation->getErrors());
    }
}
