<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\User;
use App\Model\Entity\WorkflowSetting;
use App\Policy\WorkflowSettingPolicy;
use Authorization\IdentityInterface;
use Cake\TestSuite\TestCase;

/** Vérifie les droits de paramétrage global du workflow. */
class WorkflowSettingsPolicyTest extends TestCase
{
    public function testLeParametrageGlobalEstReserveAuSuperAdministrateur(): void
    {
        $policy = new WorkflowSettingPolicy();
        $setting = new WorkflowSetting();

        $this->assertTrue($policy->canIndex($this->identity(new User(['issuperuser' => true])), $setting));
        $this->assertFalse($policy->canManage($this->identity(new User(['issuperuser' => false])), $setting));
    }

    /** @param \App\Model\Entity\User $user Utilisateur représenté par l’identité. */
    private function identity(User $user): IdentityInterface
    {
        $identity = $this->createStub(IdentityInterface::class);
        $identity->method('getOriginalData')->willReturn($user);

        return $identity;
    }
}
