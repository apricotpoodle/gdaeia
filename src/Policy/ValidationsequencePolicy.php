<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Validationsequence;
use Authorization\IdentityInterface;

/** Politique de l'administration de la configuration des validations. */
class ValidationsequencePolicy extends AppPolicy
{
    /** @param \Authorization\IdentityInterface $identity @return bool */
    public function canIndex(IdentityInterface $identity): bool
    {
        return $this->isSuperAdministrator($identity);
    }

    /** @param \Authorization\IdentityInterface $identity @param \App\Model\Entity\Validationsequence $sequence @return bool */
    public function canManage(IdentityInterface $identity, Validationsequence $sequence): bool
    {
        return $this->isSuperAdministrator($identity);
    }

    /** @param \Authorization\IdentityInterface $identity @return bool */
    private function isSuperAdministrator(IdentityInterface $identity): bool
    {
        return (bool)$this->getValidUser($identity)?->get('issuperuser');
    }
}
