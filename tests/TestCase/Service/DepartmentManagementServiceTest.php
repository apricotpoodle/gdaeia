<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Model\Entity\Department;
use App\Service\DepartmentManagementService;
use Cake\TestSuite\TestCase;
use DomainException;

final class DepartmentManagementServiceTest extends TestCase
{
    protected array $fixtures = [
        'app.Departments',
        'app.CgrCodes',
        'app.CgrStrategies',
        'app.Users',
        'app.UserDepartments',
        'app.Applicationforms',
        'app.Validationsequences',
    ];

    public function testLeDepartementNePeutPasDevenirSonPropreParent(): void
    {
        $department = new Department([
            'id' => 2,
            'parent_id' => 1,
            'lft' => 2,
            'rght' => 3,
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('ne peut pas être son propre parent');

        (new DepartmentManagementService())->assertParentChangeAllowed($department, 2);
    }

    public function testLImpactExposeLesDependancesDuDepartement(): void
    {
        $departments = $this->getTableLocator()->get('Departments');
        $department = $departments->get(2);

        $impact = (new DepartmentManagementService())->getImpact($department);

        $this->assertArrayHasKey('dependencies', $impact);
        $this->assertArrayHasKey('blockers', $impact);
        $this->assertArrayHasKey('can_deactivate', $impact);
        $this->assertIsBool($impact['can_deactivate']);
    }
}
