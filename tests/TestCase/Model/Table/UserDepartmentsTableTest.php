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
    public function testValidationParDefaut(): void
    {
        $userDepartment = $this->UserDepartments->newEntity([
            'user_id' => 'invalide',
            'department_id' => 'invalide',
        ]);

        $this->assertArrayHasKey('user_id', $userDepartment->getErrors());
        $this->assertArrayHasKey('department_id', $userDepartment->getErrors());
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\UserDepartmentsTable::buildRules()
     */
    public function testReglesIntegrite(): void
    {
        $userDepartment = $this->UserDepartments->newEntity([
            'user_id' => 99999,
            'department_id' => 1,
        ]);

        $this->assertFalse($this->UserDepartments->save($userDepartment));
        $this->assertArrayHasKey('user_id', $userDepartment->getErrors());
    }

    public function testAjouteLesAssociationsAbsentesSansDupliquerLesExistantes(): void
    {
        $createdCount = $this->UserDepartments->getConnection()->transactional(
            fn(): int => $this->UserDepartments->addMissingAssociations([1, 2], [1, 2]),
        );

        $this->assertSame(3, $createdCount);
        $this->assertSame(4, $this->UserDepartments->find()->count());

        $this->assertSame(
            0,
            $this->UserDepartments->addMissingAssociations([1, 2], [1, 2]),
        );
    }

    public function testRemplaceLesAssociationsDesUtilisateursCiblesUniquement(): void
    {
        $createdCount = $this->UserDepartments->getConnection()->transactional(
            fn(): int => $this->UserDepartments->replaceAssociationsForUsers([1, 2], [2]),
        );

        $this->assertSame(2, $createdCount);
        $this->assertSame(0, $this->UserDepartments->find()->where(['department_id' => 1])->count());
        $this->assertSame(2, $this->UserDepartments->find()->where(['department_id' => 2])->count());
    }
}
