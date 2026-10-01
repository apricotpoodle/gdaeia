<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\User;
use App\Model\Table\FieldAuthorizationsTable;
use Authorization\IdentityInterface;

/**
 * Policy de la table des autorisations de champs.
 *
 * Cette policy reste distincte des capacités métier des demandes
 * implémentées par ApplicationformPolicy.
 */
class FieldAuthorizationsTablePolicy extends AppPolicy
{
    /**
     * Détermine si l'utilisateur peut lister les éléments (action index)
     *
     * @param \Authorization\IdentityInterface $identity L'utilisateur connecté
     * @param \App\Model\Table\FieldAuthorizationsTable $table L'instance de la table
     * @return bool
     */
    public function canIndex(IdentityInterface $identity, FieldAuthorizationsTable $table): bool
    {
        $user = $this->getValidUser($identity);

        return $user !== null && (int)$user->get('role_id') !== User::ROLE_ADMIN;
    }
}
