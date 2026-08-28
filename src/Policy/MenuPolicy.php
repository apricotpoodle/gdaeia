<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Menu;
use App\Model\Entity\User;
use Authorization\IdentityInterface;

/**
 * Class MenuPolicy
 *
 * Politiques d'accès pour la gestion de l'arborescence des menus.
 */
class MenuPolicy
{
    /**
     * Extraire l'utilisateur connecté depuis l'identité (Principe DRY).
     *
     * @param \Authorization\IdentityInterface $identity
     * @return \App\Model\Entity\User|null
     */
    private function getValidUser(IdentityInterface $identity): ?User
    {
        $user = $identity->getOriginalData();

        return $user instanceof User ? $user : null;
    }

    /**
     * Détermine si l'utilisateur possède les droits d'administration globaux.
     *
     * @param \Authorization\IdentityInterface $identity
     * @return bool
     */
    private function isAdmin(IdentityInterface $identity): bool
    {
        $user = $this->getValidUser($identity);

        return $user !== null && (
            (bool)$user->get('issuperuser') ||
            $user->get('role_id') === User::ROLE_ADMIN
        );
    }

    /**
     * Autorisation pour la liste (index)
     *
     * @param \Authorization\IdentityInterface $identity
     * @return bool
     */
    public function canIndex(IdentityInterface $identity): bool
    {
        return $this->isAdmin($identity);
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
        return $this->isAdmin($identity);
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
        return $this->isAdmin($identity);
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
        return $this->isAdmin($identity);
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
        return $this->isAdmin($identity);
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
        return $this->isAdmin($identity);
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
        return $this->isAdmin($identity);
    }
}
