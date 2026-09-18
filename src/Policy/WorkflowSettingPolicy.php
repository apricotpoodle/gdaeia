<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\WorkflowSetting;
use Authorization\IdentityInterface;

/** Politique du paramétrage global du workflow. */
class WorkflowSettingPolicy extends AppPolicy
{
    /** @param \Authorization\IdentityInterface $identity @param \App\Model\Entity\WorkflowSetting $setting @return bool */
    public function canIndex(IdentityInterface $identity, WorkflowSetting $setting): bool
    {
        return (bool)$this->getValidUser($identity)?->get('issuperuser');
    }

    /** @param \Authorization\IdentityInterface $identity @param \App\Model\Entity\WorkflowSetting $setting @return bool */
    public function canManage(IdentityInterface $identity, WorkflowSetting $setting): bool
    {
        return $this->canIndex($identity, $setting);
    }
}
