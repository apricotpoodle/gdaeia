<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Permission;
use App\Service\Security\PermissionResolver;
use Authorization\IdentityInterface;

/** Protège l’administration du catalogue et des attributions de permissions. */
final class PermissionPolicy extends AppPolicy
{
    /** Construit la policy avec son résolveur de permissions. */
    public function __construct(private readonly PermissionResolver $permissions = new PermissionResolver())
    {
    }

    /** Autorise la consultation du catalogue. */
    public function canIndex(IdentityInterface $identity, Permission $permission): bool
    {
        return $this->hasManagementPermission($identity);
    }

    /** Autorise l’administration des attributions. */
    public function canManage(IdentityInterface $identity, Permission $permission): bool
    {
        return $this->hasManagementPermission($identity);
    }

    /** Vérifie la permission technique d’administration. */
    private function hasManagementPermission(IdentityInterface $identity): bool
    {
        $user = $this->getValidUser($identity);

        return $user !== null && $this->permissions->allows($user, 'menus.manage', PermissionResolver::SCOPE_UNSCOPED);
    }
}
