<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\Role;
use App\Model\Entity\User;
use App\Policy\RolePolicy;
use Cake\TestSuite\TestCase;

class RolePolicyTest extends TestCase
{
    public function testSeulLeSuperAdministrateurGereLesRoles(): void
    {
        $policy = new RolePolicy();
        $role = new Role(['base' => false]);

        $this->assertFalse($policy->canIndex(new User(['role_id' => User::ROLE_ADMIN])));
        $this->assertTrue($policy->canIndex(new User(['issuperuser' => true])));
        $this->assertTrue($policy->canEdit(new User(['issuperuser' => true]), $role));
    }

    public function testUnRoleSocleNePeutEtreNiModifieNiDesactive(): void
    {
        $policy = new RolePolicy();
        $superAdministrator = new User(['issuperuser' => true]);
        $baseRole = new Role(['base' => true]);

        $this->assertFalse($policy->canEdit($superAdministrator, $baseRole));
        $this->assertFalse($policy->canDelete($superAdministrator, $baseRole));
    }
}
