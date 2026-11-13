<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\Department;
use App\Model\Entity\User;
use App\Policy\DepartmentPolicy;
use App\Service\Security\PermissionResolver;
use Cake\TestSuite\TestCase;
use DateTimeImmutable;

final class DepartmentPolicyTest extends TestCase
{
    public function testLeSuperAdministrateurGereUnDepartementActif(): void
    {
        $policy = new DepartmentPolicy(new PermissionResolver([]));
        $department = new Department(['base' => false, 'deleted' => null]);
        $superAdministrator = new User(['issuperuser' => true]);

        $this->assertTrue($policy->canIndex($superAdministrator));
        $this->assertTrue($policy->canView($superAdministrator, $department));
        $this->assertTrue($policy->canAdd($superAdministrator, $department));
        $this->assertTrue($policy->canEdit($superAdministrator, $department));
        $this->assertTrue($policy->canDeactivate($superAdministrator, $department));
        $this->assertTrue($policy->canViewImpacts($superAdministrator, $department));
    }

    public function testUnOperateurNePeutPasAdministrerLesDepartements(): void
    {
        $policy = new DepartmentPolicy(new PermissionResolver([]));
        $department = new Department(['base' => false, 'deleted' => null]);
        $operator = new User(['issuperuser' => false]);

        $this->assertFalse($policy->canIndex($operator));
        $this->assertFalse($policy->canView($operator, $department));
        $this->assertFalse($policy->canAdd($operator, $department));
        $this->assertFalse($policy->canEdit($operator, $department));
        $this->assertFalse($policy->canDeactivate($operator, $department));
        $this->assertFalse($policy->canViewImpacts($operator, $department));
    }

    public function testUnAdministrateurAvecLaPermissionGereLesDepartements(): void
    {
        $policy = new DepartmentPolicy(new PermissionResolver([
            'departments.read' => [PermissionResolver::SCOPE_ALL],
            'departments.manage' => [PermissionResolver::SCOPE_ALL],
        ]));
        $department = new Department(['base' => false, 'deleted' => null]);
        $administrator = new User(['id' => 2, 'role_id' => User::ROLE_ADMIN, 'issuperuser' => false]);

        self::assertTrue($policy->canIndex($administrator));
        self::assertTrue($policy->canEdit($administrator, $department));
    }

    public function testUnAdministrateurOrdinaireNePeutPasModifierUnDepartementSocle(): void
    {
        $policy = new DepartmentPolicy(new PermissionResolver([
            'departments.manage' => [PermissionResolver::SCOPE_ALL],
        ]));
        $department = new Department(['base' => true, 'deleted' => null]);
        $administrator = new User(['id' => 2, 'role_id' => User::ROLE_ADMIN, 'issuperuser' => false]);

        self::assertFalse($policy->canEdit($administrator, $department));
    }

    public function testLeSuperAdministrateurPeutModifierUnDepartementSocle(): void
    {
        $policy = new DepartmentPolicy(new PermissionResolver([]));
        $department = new Department(['base' => true, 'deleted' => null]);
        $superAdministrator = new User(['issuperuser' => true]);

        self::assertTrue($policy->canEdit($superAdministrator, $department));
    }

    public function testUnDepartementDejaDesactiveNePeutPasEtreDesactive(): void
    {
        $policy = new DepartmentPolicy();
        $department = new Department(['base' => false, 'deleted' => new DateTimeImmutable()]);
        $superAdministrator = new User(['issuperuser' => true]);

        $this->assertFalse($policy->canDeactivate($superAdministrator, $department));
    }
}
