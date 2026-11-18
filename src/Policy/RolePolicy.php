<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Role;
use Authorization\IdentityInterface;

/** Politique d'administration du référentiel sensible des rôles. */
class RolePolicy extends AdministrationPolicy
{
    /** @param \Authorization\IdentityInterface $identity @return bool */
    public function canIndex(IdentityInterface $identity): bool
    {
        return $this->allowsAdministration($identity, 'roles.read');
    }

    /** @param \Authorization\IdentityInterface $identity @param \App\Model\Entity\Role $role @return bool */
    public function canView(IdentityInterface $identity, Role $role): bool
    {
        return $this->allowsAdministration($identity, 'roles.read');
    }

    /** @param \Authorization\IdentityInterface $identity @param \App\Model\Entity\Role $role @return bool */
    public function canAdd(IdentityInterface $identity, Role $role): bool
    {
        return $this->allowsAdministration($identity, 'roles.create');
    }

    /** Vérifie la permission de modification des rôles. */
    public function canEdit(IdentityInterface $identity, Role $role): bool
    {
        return $this->allowsAdministration($identity, 'roles.update');
    }

    /** La suppression est logique ; un rôle socle ne peut jamais être désactivé. */
    public function canDelete(IdentityInterface $identity, Role $role): bool
    {
        return $this->allowsAdministration($identity, 'roles.delete') && !$role->get(Role::FIELD_BASE);
    }
}
