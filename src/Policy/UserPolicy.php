<?php
declare(strict_types=1);

/**
 * @file src/Policy/UserPolicy.php
 */

namespace App\Policy;

use App\Model\Entity\User;
use App\Service\Security\PermissionResolver;
use Authorization\IdentityInterface;
use Cake\ORM\TableRegistry;

/**
 * Users policy
 */
class UserPolicy extends AppPolicy
{
    /** Construit la Policy avec le résolveur commun. */
    public function __construct(private readonly PermissionResolver $permissions = new PermissionResolver())
    {
    }

    /**
     * Check if $user can list Users
     *
     * @param \Authorization\IdentityInterface $identity The user.
     * @return bool
     */
    public function canIndex(IdentityInterface $identity): bool
    {
        // Vrai si Super Admin ET que la cible n'est PAS un Super Admin
        $user = $this->getValidUser($identity);
        if (!$user) {
            return false; // Par sécurité, on bloque si ce n'est pas un User valide
        }

        return $this->permissions->allowsEntry($user, 'users.read');
    }

    /**
     * Détermine si l'opérateur a le droit de créer un utilisateur.
     *
     * @param \Authorization\IdentityInterface $identity L'opérateur connecté.
     * @return bool
     */
    public function canAdd(IdentityInterface $identity): bool
    {
        // Vrai si Super Admin ET que la cible n'est PAS un Super Admin
        /** @var \App\Model\Entity\User $user */
        $user = $this->getValidUser($identity);
        if (!$user) {
            return false; // Par sécurité, on bloque si ce n'est pas un User valide
        }

        return $this->permissions->allows($user, 'users.create', PermissionResolver::SCOPE_UNSCOPED)
            && ($user->isSuperUser() || $user->hasRole(User::ALLOWED_ROLES_FOR_CREATE));
    }

    /**
     * Check if $user can impersonate Users
     *
     * @param \Authorization\IdentityInterface $identity The user.
     * @param \App\Model\Entity\User $target
     * @return bool
     */
    public function canImpersonate(IdentityInterface $identity, User $target): bool
    {
        // Vrai si Super Admin ET que la cible n'est PAS un Super Admin
        $user = $this->getValidUser($identity);
        if (!$user) {
            return false; // Par sécurité, on bloque si ce n'est pas un User valide
        }

        // 1. VERROU STRICT : Si l'utilisateur est DÉJÀ en mode impersonate, interdiction d'enchaîner
        if ($this->isImpersonating($identity)) {
            return false; // Interdit d'usurper en cascade s'il y a déjà une session d'usurpation active !
        }

        // 2. Condition standard : Seul un Super Admin peut usurper un utilisateur non Super Admin
        return $this->permissions->allows($user, 'users.impersonate', PermissionResolver::SCOPE_UNSCOPED)
            && $user->isSuperUser()
            && ($user->id != $target->id);
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
        if (!$user) {
            return false; // Par sécurité, on bloque si ce n'est pas un User valide
        }

        return $this->permissions->allowsUser($user, 'users.update', $target)
            && ($user->isSuperUser()
                || $user->id === $target->id
                || ($user->hasRole($user::ALLOWED_ROLES_FOR_EDIT) && $this->isManageable($user, $target)));
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
        if (!$user) {
            return false;
        }

        return $this->permissions->allows($user, 'users.delete', PermissionResolver::SCOPE_UNSCOPED)
            && ($user->id !== $target->id)
            && ($user->isSuperUser() || $user->hasRole($user::ALLOWED_ROLES_FOR_DELETE));
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
        $user = $this->getValidUser($identity);
        if (!$user) {
            return false;
        }

        return $this->permissions->allowsUser($user, 'users.read', $target)
            && ($user->isSuperUser() || $user->id == $target->id || $user->hasRole($user::ALLOWED_ROLES_FOR_VIEW));
    }

    /** Autorise l’association d’un responsable actif à un département autorisé. */
    public function canAssociate(IdentityInterface $identity, User $target): bool
    {
        $user = $this->getValidUser($identity);
        if (!$user) {
            return false;
        }

        if ($target->get(User::FIELD_DELETED) !== null) {
            return false;
        }

        return $this->permissions->allows(
            $user,
            'users.associate_departments',
            PermissionResolver::SCOPE_ASSIGNED,
        ) && ($user->isSuperUser()
            || ($user->hasRole(User::ROLE_ADMIN)
                && ($target->isNew() || $this->isManageable($user, $target))));
    }

    /**
     * Autorise une attribution de rôle après hydratation de la cible.
     *
     * @param \Authorization\IdentityInterface $identity Opérateur courant.
     * @param \App\Model\Entity\User $target État final de la cible.
     * @return bool Vrai si la transition est autorisée.
     */
    public function canChangeRole(IdentityInterface $identity, User $target): bool
    {
        $user = $this->getValidUser($identity);
        if (!$user) {
            return false;
        }

        if (!$this->permissions->allows($user, 'users.change_role', PermissionResolver::SCOPE_UNSCOPED)) {
            return false;
        }

        if ($user->isSuperUser()) {
            return true;
        }

        if (!$user->hasRole(User::ROLE_ADMIN)) {
            return false;
        }

        if ($target->isNew()) {
            return !$target->isSuperUser()
                && in_array((int)$target->get(User::FIELD_ROLE_ID), User::FUNCTIONAL_ROLE_IDS, true);
        }

        return $this->isManageable($user, $target)
            && !$target->isSuperUser()
            && in_array((int)$target->get(User::FIELD_ROLE_ID), User::FUNCTIONAL_ROLE_IDS, true);
    }

    /**
     * Vérifie que la cible appartient au périmètre de gérabilité de l'opérateur.
     *
     * @param \App\Model\Entity\User $operator Opérateur courant.
     * @param \App\Model\Entity\User $target Utilisateur ciblé.
     * @return bool Vrai si la cible est gérable.
     */
    private function isManageable(User $operator, User $target): bool
    {
        $targetId = $target->get(User::FIELD_ID);
        if ($targetId === null) {
            return false;
        }

        $users = TableRegistry::getTableLocator()->get('Users');

        return $users->find('manageableTo', user: $operator)
            ->where(['Users.' . User::FIELD_ID => $targetId])
            ->count() > 0;
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
