<?php

declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\FieldAuthorization;
use App\Model\Entity\User;
use Authorization\IdentityInterface;

/**
 * Class FieldAuthorizationPolicy
 *
 * Politiques d'accès pour la gestion de la sécurité des champs.
 */
class FieldAuthorizationPolicy
{
    /**
     * Extraire l'utilisateur connecté depuis l'identité.
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
     * Autorisation pour la liste (index)
     *
     * @param \Authorization\IdentityInterface $identity
     * @return bool
     */
    public function canIndex(IdentityInterface $identity): bool
    {
        $user = $this->getValidUser($identity);

        return $user !== null && (bool)$user->get('issuperuser');
    }

    /**
     * Autorisation pour l'affichage d'un élément (view)
     *
     * @param \Authorization\IdentityInterface $identity
     * @param \App\Model\Entity\FieldAuthorization $record
     * @return bool
     */
    public function canView(IdentityInterface $identity, FieldAuthorization $record): bool
    {
        $user = $this->getValidUser($identity);

        return $user !== null && (bool)$user->get('issuperuser');
    }

    /**
     * Autorisation pour l'ajout (add)
     *
     * @param \Authorization\IdentityInterface $identity
     * @return bool
     */
    public function canAdd(IdentityInterface $identity): bool
    {
        $user = $this->getValidUser($identity);

        return $user !== null && (bool)$user->get('issuperuser');
    }

    /**
     * Autorisation pour l'édition (edit)
     *
     * @param \Authorization\IdentityInterface $identity
     * @param \App\Model\Entity\FieldAuthorization $record
     * @return bool
     */
    public function canEdit(IdentityInterface $identity, FieldAuthorization $record): bool
    {
        $user = $this->getValidUser($identity);

        return $user !== null && (bool)$user->get('issuperuser');
    }

    /**
     * Autorisation pour la suppression (delete)
     *
     * @param \Authorization\IdentityInterface $identity
     * @param \App\Model\Entity\FieldAuthorization $record
     * @return bool
     */
    public function canDelete(IdentityInterface $identity, FieldAuthorization $record): bool
    {
        $user = $this->getValidUser($identity);

        return $user !== null && (bool)$user->get('issuperuser');
    }
}