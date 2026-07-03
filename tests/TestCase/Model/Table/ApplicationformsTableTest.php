<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ApplicationformsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ApplicationformsTable Test Case
 */
class ApplicationformsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ApplicationformsTable
     */
    protected $Applicationforms;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Applicationforms',
        'app.Departments',
        'app.Users',
        'app.Contracttypes',
        'app.Hiringreasons',
        'app.Budgetfeatures',
        'app.Professionalcategories',
        'app.Worktimes',
        'app.Periods',
        'app.Yesnos',
        'app.Applicationformstatuses',
        'app.Applicationvalidationsteps',
        'app.Currentvalidationroles',
        'app.ValidationVisas',
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
        $config = $this->getTableLocator()->exists('Applicationforms') ? [] : ['className' => ApplicationformsTable::class];
        $this->Applicationforms = $this->getTableLocator()->get('Applicationforms', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Applicationforms);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ApplicationformsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ApplicationformsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
