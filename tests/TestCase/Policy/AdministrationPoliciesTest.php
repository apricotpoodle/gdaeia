<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\FieldAuthorization;
use App\Model\Entity\Menu;
use App\Model\Entity\User;
use App\Policy\FieldAuthorizationPolicy;
use App\Policy\MenuPolicy;
use Cake\TestSuite\TestCase;

class AdministrationPoliciesTest extends TestCase
{
    public function testLesAutorisationsDeChampSontReserveesAuxSuperAdministrateurs(): void
    {
        $policy = new FieldAuthorizationPolicy();
        $record = new FieldAuthorization();

        $this->assertFalse($policy->canIndex(new User(['role_id' => User::ROLE_ADMIN])));
        $this->assertTrue($policy->canIndex(new User(['issuperuser' => true])));
        $this->assertTrue($policy->canEdit(new User(['issuperuser' => true]), $record));
    }

    public function testLesMenusSontReservesAuxSuperAdministrateurs(): void
    {
        $policy = new MenuPolicy();
        $menu = new Menu();

        $this->assertFalse($policy->canEdit(new User(['role_id' => User::ROLE_DEMANDEUR]), $menu));
        $this->assertFalse($policy->canIndex(new User(['role_id' => User::ROLE_ADMIN])));
        $this->assertFalse($policy->canRoleAccess(new User(['role_id' => User::ROLE_ADMIN])));
        $this->assertFalse($policy->canView(new User(['role_id' => User::ROLE_ADMIN]), $menu));
        $this->assertFalse($policy->canAdd(new User(['role_id' => User::ROLE_ADMIN]), $menu));
        $this->assertFalse($policy->canMoveUp(new User(['role_id' => User::ROLE_ADMIN]), $menu));
        $this->assertFalse($policy->canMoveDown(new User(['role_id' => User::ROLE_ADMIN]), $menu));
        $this->assertFalse($policy->canDelete(new User(['role_id' => User::ROLE_ADMIN]), $menu));
        $this->assertTrue($policy->canDelete(new User(['issuperuser' => true]), $menu));
    }
}
