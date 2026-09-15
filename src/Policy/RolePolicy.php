<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Role;
use Authorization\IdentityInterface;

/** Politique d'administration du référentiel sensible des rôles. */
class RolePolicy extends AppPolicy
{
    /** @param \Authorization\IdentityInterface $identity @return bool */
    public function canIndex(IdentityInterface $identity): bool
    {
        return $this->isSuperAdministrator($identity);
    }

    /** @param \Authorization\IdentityInterface $identity @param \App\Model\Entity\Role $role @return bool */
    public function canView(IdentityInterface $identity, Role $role): bool
    {
        return $this->isSuperAdministrator($identity);
    }

    /** @param \Authorization\IdentityInterface $identity @param \App\Model\Entity\Role $role @return bool */
    public function canAdd(IdentityInterface $identity, Role $role): bool
    {
        return $this->isSuperAdministrator($identity);
    }

    /** Les rôles socles sont immuables afin de préserver la cartographie métier. */
    public function canEdit(IdentityInterface $identity, Role $role): bool
    {
        return $this->isSuperAdministrator($identity) && !$role->get('base');
    }

    /** La suppression est logique ; un rôle socle ne peut jamais être désactivé. */
    public function canDelete(IdentityInterface $identity, Role $role): bool
    {
        return $this->isSuperAdministrator($identity) && !$role->get('base');
    }

    /** @param \Authorization\IdentityInterface $identity @return bool */
    private function isSuperAdministrator(IdentityInterface $identity): bool
    {
        return (bool)$this->getValidUser($identity)?->get('issuperuser');
    }
}
