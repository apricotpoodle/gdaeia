<?php

declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\FieldAuthorization;
use App\Model\Entity\User;
use Authorization\IdentityInterface;

/**
 * Class FieldAuthorizationPolicy
 *
 * Définit les règles d'accès métier pour l'administration de la sécurité des champs.
 *
 * @package App\Policy
 */
class FieldAuthorizationPolicy
{
    /**
     * Méthode utilitaire pour extraire et vérifier l'entité User.
     *
     * @param \Authorization\IdentityInterface $identity L'identité connectée.
     * @return \App\Model\Entity\User|null
     */
    private function getValidUser(IdentityInterface $identity): ?User
    {
        $user = $identity->getOriginalData();

        return $user instanceof User ? $user : null;
    }

    /**
     * Vérifie si l'utilisateur a le droit de lister les règles de sécurité.
     *
     * @param \Authorization\IdentityInterface $identity L'opérateur connecté.
     * @return bool
     */
    public function canIndex(IdentityInterface $identity): bool
    {
        $user = $this->getValidUser($identity);

        return $user !== null && (bool)$user->get('issuperuser');
    }

    /**
     * Vérifie si l'utilisateur a le droit d'ajouter une règle de sécurité.
     *
     * @param \Authorization\IdentityInterface $identity L'opérateur connecté.
     * @return bool
     */
    public function canAdd(IdentityInterface $identity): bool
    {
        $user = $this->getValidUser($identity);

        return $user !== null && (bool)$user->get('issuperuser');
    }

    /**
     * Vérifie si l'utilisateur a le droit d'éditer une règle de sécurité.
     *
     * @param \Authorization\IdentityInterface $identity L'opérateur connecté.
     * @param \App\Model\Entity\FieldAuthorization $record La règle ciblée.
     * @return bool
     */
    public function canEdit(IdentityInterface $identity, FieldAuthorization $record): bool
    {
        $user = $this->getValidUser($identity);

        return $user !== null && (bool)$user->get('issuperuser');
    }

    /**
     * Vérifie si l'utilisateur a le droit de supprimer une règle de sécurité.
     *
     * @param \Authorization\IdentityInterface $identity L'opérateur connecté.
     * @param \App\Model\Entity\FieldAuthorization $record La règle ciblée.
     * @return bool
     */
    public function canDelete(IdentityInterface $identity, FieldAuthorization $record): bool
    {
        $user = $this->getValidUser($identity);

        return $user !== null && (bool)$user->get('issuperuser');
    }
}