<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ApplicationformstatusesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ApplicationformstatusesTable Test Case
 */
class ApplicationformstatusesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ApplicationformstatusesTable
     */
    protected $Applicationformstatuses;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Applicationforms',
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
        $config = $this->getTableLocator()->exists('Applicationformstatuses') ? [] : ['className' => ApplicationformstatusesTable::class];
        $this->Applicationformstatuses = $this->getTableLocator()->get('Applicationformstatuses', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Applicationformstatuses);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ApplicationformstatusesTable::validationDefault()
     */
    public function testValidationParDefaut(): void
    {
        $entity = $this->Applicationformstatuses->newEntity(['applicationform_id' => -1, 'has_validations' => 'invalide', 'validationstatus_id' => 'invalide', 'en_cours' => 'invalide', 'accepted' => 'invalide', 'rejected' => 'invalide']);
        $this->assertArrayHasKey('applicationform_id', $entity->getErrors());
        $this->assertArrayHasKey('has_validations', $entity->getErrors());
        $this->assertArrayHasKey('validationstatus_id', $entity->getErrors());
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ApplicationformstatusesTable::buildRules()
     */
    public function testReglesIntegrite(): void
    {
        $this->assertTrue($this->Applicationformstatuses->associations()->has('Applicationforms'));
        $this->assertTrue($this->Applicationformstatuses->associations()->has('Validationstatuses'));
    }
}
