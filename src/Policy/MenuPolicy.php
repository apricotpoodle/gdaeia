<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Menu;
use App\Service\Security\PermissionResolver;
use Authorization\IdentityInterface;

/**
 * Class MenuPolicy
 *
 * Politiques d'accès pour la gestion de l'arborescence des menus.
 */
class MenuPolicy extends AppPolicy
{
    /** Construit la policy avec son résolveur de permissions. */
    public function __construct(private readonly PermissionResolver $permissions = new PermissionResolver())
    {
    }

    /**
     * Autorisation pour la liste (index)
     *
     * @param \Authorization\IdentityInterface $identity
     * @return bool
     */
    public function canIndex(IdentityInterface $identity): bool
    {
        return $this->canManageMenus($identity);
    }

    /**
     * Autorisation dédiée à l'attribution des options de menu aux rôles.
     *
     * @param \Authorization\IdentityInterface $identity
     * @return bool
     */
    public function canRoleAccess(IdentityInterface $identity): bool
    {
        return $this->canManageMenus($identity);
    }

    /**
     * Autorisation pour l'affichage d'un élément (view)
     *
     * @param \Authorization\IdentityInterface $identity
     * @param \App\Model\Entity\Menu $menu
     * @return bool
     */
    public function canView(IdentityInterface $identity, Menu $menu): bool
    {
        return $this->canManageMenus($identity);
    }

    /**
     * Autorisation pour l'ajout (add)
     *
     * @param \Authorization\IdentityInterface $identity
     * @param \App\Model\Entity\Menu $menu
     * @return bool
     */
    public function canAdd(IdentityInterface $identity, Menu $menu): bool
    {
        return $this->canManageMenus($identity);
    }

    /**
     * Autorisation pour l'édition et le déplacement (edit, moveUp, moveDown)
     *
     * @param \Authorization\IdentityInterface $identity
     * @param \App\Model\Entity\Menu $menu
     * @return bool
     */
    public function canEdit(IdentityInterface $identity, Menu $menu): bool
    {
        return $this->canManageMenus($identity);
    }

    /**
     * Autorisation pour la suppression (delete)
     *
     * @param \Authorization\IdentityInterface $identity
     * @param \App\Model\Entity\Menu $menu
     * @return bool
     */
    public function canDelete(IdentityInterface $identity, Menu $menu): bool
    {
        return $this->canManageMenus($identity);
    }

    /**
     * Autorisation pour monter un menu (moveUp)
     *
     * @param \Authorization\IdentityInterface $identity
     * @param \App\Model\Entity\Menu $menu
     * @return bool
     */
    public function canMoveUp(IdentityInterface $identity, Menu $menu): bool
    {
        return $this->canManageMenus($identity);
    }

    /**
     * Autorisation pour descendre un menu (moveDown)
     *
     * @param \Authorization\IdentityInterface $identity
     * @param \App\Model\Entity\Menu $menu
     * @return bool
     */
    public function canMoveDown(IdentityInterface $identity, Menu $menu): bool
    {
        return $this->canManageMenus($identity);
    }

    /** Vérifie la capacité transversale d’administration des menus. */
    private function canManageMenus(IdentityInterface $identity): bool
    {
        $user = $this->getValidUser($identity);

        return $user !== null && $this->permissions->allows($user, 'menus.manage', PermissionResolver::SCOPE_UNSCOPED);
    }
}
