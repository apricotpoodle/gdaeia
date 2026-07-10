<?php

declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\User;
use Authorization\IdentityInterface;
use Authorization\Policy\Result;

/**
 * Users policy
 */
class UserPolicy
{

    /**
     * Méthode utilitaire DRY : Extrait et garantit le type de l'identité connectée.
     * Si l'identité n'est pas un humain (ex: un démon système ou une API), renvoie null.
     */
    private function getValidUser(IdentityInterface $identity): ?User
    {
        // On récupère la donnée sous-jacente (l'entité CakePHP réelle)
        $user = $identity->getOriginalData();
        // On sécurise le typage pour PHPStan et l'IDE
        return $user instanceof User ? $user : null;
    }

    /**
     * Check if $user can list Users
     *
     * @param \Authorization\IdentityInterface $identity The user.
     * @return bool
     */
    public function canIndex(IdentityInterface $identity): bool
    {
        return true;
    }

    /**
     * Détermine si l'opérateur a le droit de créer un utilisateur.
     *
     * @param \Authorization\IdentityInterface $user L'opérateur connecté.
     * @param \App\Model\Entity\User $resource L'entité utilisateur vierge à créer.
     * @return bool
     */
    public function canAdd(IdentityInterface $user, User $resource): bool
    {
        /** @var \App\Model\Entity\User $currentUser */
        $currentUser = $user->getOriginalData();

        // Règle métier : Seul un Super Admin ou un profil "Staff/RH" (par exemple, le rôle ID 1 ou 2)
        // a le droit d'accéder au formulaire de création.
        return $currentUser->get('issuperuser') || in_array(
            $currentUser->get('role_id'),
            $currentUser::ALLOWED_ROLES_FOR_CREATE,
            true // true active la vérification stricte des types
        );
    }

    /**
     * Check if $user can imperonate Users
     *
     * @param \Authorization\IdentityInterface $identity The user.
     * @param \App\Model\Entity\User $target
     * @return bool
     */
    public function canImpersonate(IdentityInterface $identity, User $target): bool
    {
        // Vrai si Super Admin ET que la cible n'est PAS un Super Admin
        $user = $this->getValidUser($identity);
        if (!$user) return false; // Par sécurité, on bloque si ce n'est pas un User valide

        return (bool) $user->get('issuperuser') && !(bool)$target->get('issuperuser');
    }

    /**
     * Check if $user can edit Users
     *
     * @param \Authorization\IdentityInterface $identity of the operator.
     * @param \App\Model\Entity\User $target
     * @return bool
     */
    public function canEdit(IdentityInterface $identity, User $target): bool
    {
        $user = $this->getValidUser($identity);
        if (!$user) return false; // Par sécurité, on bloque si ce n'est pas un User valide

        return (bool)$user->get('issuperuser') || $user->get('id') === $target->get('id');
    }

    /**
     * Check if $user can delete Users
     *
     * @param \Authorization\IdentityInterface $identity of the operator.
     * @param \App\Model\Entity\User $target
     * @return bool
     */
    public function canDelete(IdentityInterface $identity, User $target): bool
    {
        $user = $this->getValidUser($identity);
        if (!$user) return false;

        return (bool)$user->get('issuperuser') && $user->get('id') !== $target->get('id');
    }

    /**
     * Check if $user can view Users
     *
     * @param \Authorization\IdentityInterface $identity of the operator.
     * @param \App\Model\Entity\User $target
     * @return bool
     */
    public function canView(IdentityInterface $identity, User $target): bool
    {
        return true;
    }


    /**
     * Check if a user (even unauthenticated) can access the forgot password pipeline.
     *
     * @param \Authorization\IdentityInterface|null $identity The identity context.
     * @return bool
     */
    public function canForgotPassword(?IdentityInterface $identity): bool
    {
        return true; // Toujours accessible publiquement
    }
}
