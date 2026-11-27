<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\User;
use App\Model\Entity\WorkflowSetting;
use App\Policy\WorkflowSettingPolicy;
use App\Service\Security\PermissionResolver;
use Authorization\IdentityInterface;
use Cake\TestSuite\TestCase;

/** Vérifie les droits de paramétrage global du workflow. */
class WorkflowSettingsPolicyTest extends TestCase
{
    public function testLeParametrageGlobalEstReserveAuSuperAdministrateur(): void
    {
        $policy = new WorkflowSettingPolicy(new PermissionResolver([]));
        $setting = new WorkflowSetting();

        $this->assertTrue($policy->canIndex($this->identity(new User(['issuperuser' => true])), $setting));
        $this->assertFalse($policy->canManage($this->identity(new User(['issuperuser' => false])), $setting));
    }

    public function testLaModificationDuCoefficientEstReserveeAuSuperAdministrateur(): void
    {
        $policy = new WorkflowSettingPolicy(new PermissionResolver([]));
        $setting = new WorkflowSetting();

        $this->assertTrue($policy->canManageKCoefficient(
            $this->identity(new User(['issuperuser' => true])),
            $setting,
        ));
        $this->assertFalse($policy->canManageKCoefficient(
            $this->identity(new User(['issuperuser' => false])),
            $setting,
        ));
    }

    public function testLaLectureEtLaModificationDeLaConfigurationDeDuplicationSontReserveesAuSuperAdministrateur(): void
    {
        $policy = new WorkflowSettingPolicy(new PermissionResolver([]));
        $setting = new WorkflowSetting();
        $superAdministrator = $this->identity(new User(['issuperuser' => true]));
        $operator = $this->identity(new User(['issuperuser' => false]));

        $this->assertTrue($policy->canIndexDuplicateResetFields($superAdministrator, $setting));
        $this->assertTrue($policy->canManageDuplicateResetFields($superAdministrator, $setting));
        $this->assertFalse($policy->canIndexDuplicateResetFields($operator, $setting));
        $this->assertFalse($policy->canManageDuplicateResetFields($operator, $setting));
    }

    /** @param \App\Model\Entity\User $user Utilisateur représenté par l’identité. */
    private function identity(User $user): IdentityInterface
    {
        $identity = $this->createStub(IdentityInterface::class);
        $identity->method('getOriginalData')->willReturn($user);

        return $identity;
    }
}
