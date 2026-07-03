<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UserDepartmentsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\UserDepartmentsTable Test Case
 */
class UserDepartmentsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\UserDepartmentsTable
     */
    protected $UserDepartments;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.UserDepartments',
        'app.Users',
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
        $config = $this->getTableLocator()->exists('UserDepartments') ? [] : ['className' => UserDepartmentsTable::class];
        $this->UserDepartments = $this->getTableLocator()->get('UserDepartments', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->UserDepartments);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\UserDepartmentsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\UserDepartmentsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
