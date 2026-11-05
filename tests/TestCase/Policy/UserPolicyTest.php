<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\User;
use App\Policy\UserPolicy;
use Authorization\IdentityInterface;
use Cake\TestSuite\TestCase;

class UserPolicyTest extends TestCase
{
    private UserPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new UserPolicy();
    }

    public function testLaSuppressionDeSonPropreCompteEstToujoursRefusee(): void
    {
        $admin = new User(['id' => 1, 'issuperuser' => true]);

        $this->assertFalse($this->policy->canDelete($admin, $admin));
        $this->assertTrue($this->policy->canDelete($admin, new User(['id' => 2])));
    }

    public function testLUsurpationEstReserveeAuSuperAdminEtNePeutPasEtreEnchainee(): void
    {
        $target = new User(['id' => 2]);

        $this->assertFalse($this->policy->canImpersonate(new User(['id' => 1]), $target));
        $this->assertTrue($this->policy->canImpersonate(new User(['id' => 1, 'issuperuser' => true]), $target));
        $this->assertFalse($this->policy->canImpersonate(new User([
            'id' => 1,
            'issuperuser' => true,
            'is_impersonating' => true,
            'original_admin_id' => 1,
        ]), $target));
    }

    public function testLAdministrateurPeutCreerEtModifierLesUtilisateurs(): void
    {
        $administrator = new User(['id' => 1, 'role_id' => User::ROLE_ADMIN]);
        $target = new User(['id' => 1]);

        $this->assertTrue($this->policy->canAdd($administrator));
        $this->assertTrue($this->policy->canEdit($administrator, $target));
        $this->assertTrue($this->policy->canView($administrator, $target));
    }

    public function testLesActionsRefusentUneIdentiteInvalideEtLesCasLimites(): void
    {
        $identity = $this->createStub(IdentityInterface::class);
        $identity->method('getOriginalData')->willReturn([]);
        $target = new User(['id' => 2]);

        $this->assertFalse($this->policy->canIndex($identity));
        $this->assertFalse($this->policy->canAdd($identity));
        $this->assertFalse($this->policy->canImpersonate($identity, $target));
        $this->assertFalse($this->policy->canEdit($identity, $target));
        $this->assertFalse($this->policy->canDelete($identity, $target));
        $this->assertFalse($this->policy->canView($identity, $target));
        $this->assertTrue($this->policy->canForgotPassword(null));
    }

    public function testLesRolesAutorisesPeuventCreerModifierVoirEtSupprimerUneAutreCible(): void
    {
        $operator = new User(['id' => 1, 'role_id' => User::ROLE_ADMIN]);
        $target = new User(['id' => 2]);
        $editTarget = new User(['id' => 1]);

        $this->assertTrue($this->policy->canIndex($operator));
        $this->assertTrue($this->policy->canAdd($operator));
        $this->assertTrue($this->policy->canEdit($operator, $editTarget));
        $this->assertTrue($this->policy->canView($operator, $target));
        $this->assertTrue($this->policy->canDelete($operator, $target));
    }

    public function testLeSuperAdministrateurPeutAssocierUnResponsableActif(): void
    {
        $administrator = new User(['id' => 1, 'issuperuser' => true]);
        $target = new User(['id' => 2, 'deleted' => null]);

        $this->assertTrue($this->policy->canAssociate($administrator, $target));
    }

    public function testLeRoleFonctionnelEstAttribuableSurUneCreationLocale(): void
    {
        $administrator = new User(['role_id' => User::ROLE_ADMIN, 'issuperuser' => false]);
        $target = new User([
            'role_id' => User::ROLE_DEMANDEUR,
            'issuperuser' => false,
        ]);

        $this->assertTrue($this->policy->canChangeRole($administrator, $target));
    }

    public function testLeRoleAdminEtLeStatutSuperuserSontReservesAuSuperuser(): void
    {
        $administrator = new User(['role_id' => User::ROLE_ADMIN, 'issuperuser' => false]);

        $this->assertFalse($this->policy->canChangeRole($administrator, new User([
            'role_id' => User::ROLE_ADMIN,
            'issuperuser' => false,
        ])));
        $this->assertFalse($this->policy->canChangeRole($administrator, new User([
            'role_id' => User::ROLE_DEMANDEUR,
            'issuperuser' => true,
        ])));
        $this->assertTrue($this->policy->canChangeRole(
            new User(['issuperuser' => true]),
            new User(['role_id' => User::ROLE_ADMIN, 'issuperuser' => true]),
        ));
    }
}
