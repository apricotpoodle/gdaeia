<?php
declare(strict_types=1);

namespace App\Policy;

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
        return $this->isSuperUser($identity);
    }
}
