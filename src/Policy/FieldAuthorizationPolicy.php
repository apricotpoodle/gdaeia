<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\FieldAuthorization;
use Authorization\IdentityInterface;

/**
 * Class FieldAuthorizationPolicy
 *
 * Politiques d'accès pour la gestion de la sécurité des champs.
 */
class FieldAuthorizationPolicy extends AppPolicy
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
     * Autorisation pour l'affichage d'un élément (view)
     *
     * @param \Authorization\IdentityInterface $identity
     * @param \App\Model\Entity\FieldAuthorization $record
     * @return bool
     */
    public function canView(IdentityInterface $identity, FieldAuthorization $record): bool
    {
        return $this->isSuperUser($identity);
    }

    /**
     * Autorisation pour l'ajout (add)
     *
     * @param \Authorization\IdentityInterface $identity
     * @return bool
     */
    public function canAdd(IdentityInterface $identity): bool
    {
        return $this->isSuperUser($identity);
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
        return $this->isSuperUser($identity);
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
        return $this->isSuperUser($identity);
    }
}
