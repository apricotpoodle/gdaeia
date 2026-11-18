<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\User;
use App\Policy\UserPolicy;
use App\Service\Security\PermissionResolver;
use Authorization\IdentityInterface;
use Cake\TestSuite\TestCase;

class UserPolicyTest extends TestCase
{
    private UserPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new UserPolicy(new PermissionResolver([
            'users.read' => ['self'],
            'users.update' => ['self'],
            'users.create' => ['unscoped'],
            'users.delete' => ['unscoped'],
            'users.change_role' => ['unscoped'],
            'users.associate_departments' => ['assigned'],
            'users.impersonate' => ['unscoped'],
        ]));
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
        $this->assertTrue($this->policy->canView($operator, $editTarget));
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

    public function testLaCreationExigeLaPermissionGeneriqueEtLeRoleAdmin(): void
    {
        $administrator = new User(['role_id' => User::ROLE_ADMIN]);
        $otherRole = new User(['role_id' => User::ROLE_1_VALIDEUR_POLE]);

        $withoutPermission = new UserPolicy(new PermissionResolver([]));
        $wrongScope = new UserPolicy(new PermissionResolver(['users.create' => ['assigned']]));
        $withPermission = new UserPolicy(new PermissionResolver(['users.create' => ['unscoped']]));

        $this->assertFalse($withoutPermission->canAdd($administrator));
        $this->assertFalse($wrongScope->canAdd($administrator));
        $this->assertTrue($withPermission->canAdd($administrator));
        $this->assertFalse($withPermission->canAdd($otherRole));
        $this->assertTrue($withoutPermission->canAdd(new User(['issuperuser' => true])));
    }

    public function testLaSuppressionExigeLaPermissionGeneriqueEtLeRoleAdmin(): void
    {
        $target = new User(['id' => 2]);
        $administrator = new User(['id' => 1, 'role_id' => User::ROLE_ADMIN]);
        $otherRole = new User(['id' => 3, 'role_id' => User::ROLE_1_VALIDEUR_POLE]);

        $withoutPermission = new UserPolicy(new PermissionResolver([]));
        $wrongScope = new UserPolicy(new PermissionResolver(['users.delete' => ['assigned']]));
        $withPermission = new UserPolicy(new PermissionResolver(['users.delete' => ['unscoped']]));

        $this->assertFalse($withoutPermission->canDelete($administrator, $target));
        $this->assertFalse($wrongScope->canDelete($administrator, $target));
        $this->assertTrue($withPermission->canDelete($administrator, $target));
        $this->assertFalse($withPermission->canDelete($otherRole, $target));
        $this->assertFalse($withPermission->canDelete($administrator, $administrator));
        $this->assertTrue($withoutPermission->canDelete(new User(['issuperuser' => true]), $target));
    }

    public function testLeChangementDeRoleExigeLaPermissionGeneriqueEtConserveLesLimitesDeRole(): void
    {
        $administrator = new User(['role_id' => User::ROLE_ADMIN]);
        $functionalTarget = new User(['role_id' => User::ROLE_DEMANDEUR]);
        $adminTarget = new User(['role_id' => User::ROLE_ADMIN]);

        $withoutPermission = new UserPolicy(new PermissionResolver([]));
        $wrongScope = new UserPolicy(new PermissionResolver(['users.change_role' => ['assigned']]));
        $withPermission = new UserPolicy(new PermissionResolver(['users.change_role' => ['unscoped']]));

        $this->assertFalse($withoutPermission->canChangeRole($administrator, $functionalTarget));
        $this->assertFalse($wrongScope->canChangeRole($administrator, $functionalTarget));
        $this->assertTrue($withPermission->canChangeRole($administrator, $functionalTarget));
        $this->assertFalse($withPermission->canChangeRole($administrator, $adminTarget));
        $this->assertFalse($withPermission->canChangeRole(
            new User(['role_id' => User::ROLE_1_VALIDEUR_POLE]),
            $functionalTarget,
        ));
        $this->assertTrue($withoutPermission->canChangeRole(
            new User(['issuperuser' => true]),
            $adminTarget,
        ));
    }

    public function testLAssociationDeDepartementsExigeLePerimetreAssigneEtLeRoleAdmin(): void
    {
        $administrator = new User(['role_id' => User::ROLE_ADMIN]);
        $otherRole = new User(['role_id' => User::ROLE_1_VALIDEUR_POLE]);
        $target = new User();

        $withoutPermission = new UserPolicy(new PermissionResolver([]));
        $wrongScope = new UserPolicy(new PermissionResolver([
            'users.associate_departments' => ['unscoped'],
        ]));
        $withPermission = new UserPolicy(new PermissionResolver([
            'users.associate_departments' => ['assigned'],
        ]));

        $this->assertFalse($withoutPermission->canAssociate($administrator, $target));
        $this->assertFalse($wrongScope->canAssociate($administrator, $target));
        $this->assertTrue($withPermission->canAssociate($administrator, $target));
        $this->assertFalse($withPermission->canAssociate($otherRole, $target));
        $this->assertTrue($withoutPermission->canAssociate(new User(['issuperuser' => true]), $target));
    }

    public function testLUsurpationNEstPasAccordeeParUneAttributionOrdinaire(): void
    {
        $policy = new UserPolicy(new PermissionResolver(['users.impersonate' => ['unscoped']]));
        $target = new User(['id' => 2]);

        $this->assertFalse($policy->canImpersonate(new User([
            'id' => 1,
            'role_id' => User::ROLE_ADMIN,
        ]), $target));
        $this->assertFalse($policy->canImpersonate(new User([
            'id' => 1,
            'issuperuser' => true,
            'is_impersonating' => true,
            'original_admin_id' => 1,
        ]), $target));
        $this->assertTrue($policy->canImpersonate(new User([
            'id' => 1,
            'issuperuser' => true,
        ]), $target));
    }
}
