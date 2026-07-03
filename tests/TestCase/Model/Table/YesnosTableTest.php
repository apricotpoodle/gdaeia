<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\YesnosTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\YesnosTable Test Case
 */
class YesnosTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\YesnosTable
     */
    protected $Yesnos;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Yesnos',
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
        $config = $this->getTableLocator()->exists('Yesnos') ? [] : ['className' => YesnosTable::class];
        $this->Yesnos = $this->getTableLocator()->get('Yesnos', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Yesnos);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\YesnosTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\YesnosTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
