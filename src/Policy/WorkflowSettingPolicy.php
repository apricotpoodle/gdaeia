<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\User;
use App\Model\Entity\WorkflowSetting;
use Authorization\IdentityInterface;

/** Politique du paramétrage global du workflow. */
class WorkflowSettingPolicy extends AdministrationPolicy
{
    /** @param \Authorization\IdentityInterface $identity @param \App\Model\Entity\WorkflowSetting $setting @return bool */
    public function canIndex(IdentityInterface $identity, WorkflowSetting $setting): bool
    {
        return $this->allowsAdministration($identity, 'workflowsettings.read');
    }

    /** @param \Authorization\IdentityInterface $identity @param \App\Model\Entity\WorkflowSetting $setting @return bool */
    public function canManage(IdentityInterface $identity, WorkflowSetting $setting): bool
    {
        return $this->allowsAdministration($identity, 'workflowsettings.update');
    }

    /** @param \Authorization\IdentityInterface $identity @param \App\Model\Entity\WorkflowSetting $setting @return bool */
    public function canManageKCoefficient(IdentityInterface $identity, WorkflowSetting $setting): bool
    {
        $user = $identity->getOriginalData();

        return $user instanceof User && $user->isSuperUser();
    }
}
