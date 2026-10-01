<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Menu;
use Authorization\IdentityInterface;

/**
 * Class MenuPolicy
 *
 * Politiques d'accès pour la gestion de l'arborescence des menus.
 */
class MenuPolicy extends AppPolicy
{
    /**
     * Autorisation pour la liste (index)
     *
     * @param \Authorization\IdentityInterface $identity
     * @return bool
     */
    public function canIndex(IdentityInterface $identity): bool
    {
        return $this->isSuperUser($identity);
    }

    /**
     * Autorisation dédiée à l'attribution des options de menu aux rôles.
     *
     * @param \Authorization\IdentityInterface $identity
     * @return bool
     */
    public function canRoleAccess(IdentityInterface $identity): bool
    {
        return $this->isSuperUser($identity);
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
        return $this->isSuperUser($identity);
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
        return $this->isSuperUser($identity);
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
        return $this->isSuperUser($identity);
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
        return $this->isSuperUser($identity);
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
        return $this->isSuperUser($identity);
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
        return $this->isSuperUser($identity);
    }
}
