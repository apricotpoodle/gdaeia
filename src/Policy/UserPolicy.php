<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\User;
use Authentication\Authenticator\AuthenticatorInterface;
use Authentication\Identity;
use Authorization\IdentityInterface;
use Cake\Routing\Router;

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
        // Vrai si Super Admin ET que la cible n'est PAS un Super Admin
        $user = $this->getValidUser($identity);
        if (!$user) {
            return false; // Par sécurité, on bloque si ce n'est pas un User valide
        }

        return true;
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

        // Règle métier : Seul un Super Admin ou un profil "Staff/RH" (par exemple, le rôle ID 1 ou 2)
        // a le droit d'accéder au formulaire de création.
        return $user->get('issuperuser') || in_array(
            $user->get('role_id'),
            $user::ALLOWED_ROLES_FOR_CREATE,
            true, // true active la vérification stricte des types
        );
    }

    /**
     * Vérifie si l'utilisateur courant est déjà dans un état d'impersonation.
     * Inspecte l'instance d'identité de manière totalement étanche (sans dépendre de la Request/Session).
     *
     * @param \Authorization\IdentityInterface $identity
     * @return bool
     */
    private function isAlreadyImpersonating(IdentityInterface $identity): bool
    {
        /** @var Authentication $originalData */
        $originalData = $identity->getOriginalData();

        // 1. Détection via l'objet Authentication\Identity (si le décorateur contient l'attribut d'impersonation)
        if ($originalData instanceof Identity && method_exists($originalData, 'isImpersonating')) {
            /** @var Authentication $originalData */
            return $originalData->isImpersonating();
        }

        // 2. Détection via attribut/propriété d'impersonation stockée dans l'identité
        if ($identity->offsetExists('impersonator') || $identity->offsetExists('_impersonator')) {
            return true;
        }

        // 3. Inspection défensive du tableau de données sous-jacent de l'objet Identity
        if ($originalData instanceof User && isset($originalData['_impersonator'])) {
            return true;
        }

        return false;
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
        if (!$user) {
            return false; // Par sécurité, on bloque si ce n'est pas un User valide
        }

        // 1. VERROU STRICT : Si l'utilisateur est DÉJÀ en mode impersonate, interdiction d'enchaîner
        if ($this->isAlreadyImpersonating($identity)) {
            return false; // Interdit d'usurper en cascade s'il y a déjà une session d'usurpation active !
        }
        // 2. Condition standard : Seul un Super Admin peut usurper un utilisateur non Super Admin
        return (bool)$user->get('issuperuser') && $user->get('id') != $target->get('id');
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

        return (bool)$user->get('issuperuser')
            || $user->get('id') === $target->get('id')
            || $user->hasRole($user::ALLOWED_ROLES_FOR_EDIT);
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

        return
            $user->get('id') !== $target->get('id') && (
                (bool)$user->get('issuperuser')
                || $user->hasRole($user::ALLOWED_ROLES_FOR_DELETE)
            );
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

        return (bool)$user->get('issuperuser') || $user->get('id') == $target->get('id')
            || $user->hasRole($user::ALLOWED_ROLES_FOR_VIEW);
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
