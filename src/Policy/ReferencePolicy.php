<?php
declare(strict_types=1);

namespace App\Policy;

use Authorization\IdentityInterface;
use Cake\Datasource\EntityInterface;

/** Politique commune aux nomenclatures administrables. */
abstract class ReferencePolicy extends AppPolicy
{
    /** Autorise l’accès à la grille. */
    public function canIndex(IdentityInterface $identity): bool
    {
        return $this->isSuperUser($identity);
    }

    /** Autorise la consultation d’une ligne. */
    public function canView(IdentityInterface $identity, EntityInterface $entity): bool
    {
        return $this->isSuperUser($identity);
    }

    /** Autorise la création d’une ligne. */
    public function canAdd(IdentityInterface $identity, EntityInterface $entity): bool
    {
        return $this->isSuperUser($identity);
    }

    /** Interdit la modification des lignes socles. */
    public function canEdit(IdentityInterface $identity, EntityInterface $entity): bool
    {
        return $this->isSuperUser($identity) && !$entity->get('base');
    }

    /** Interdit la suppression des lignes socles. */
    public function canDelete(IdentityInterface $identity, EntityInterface $entity): bool
    {
        return $this->isSuperUser($identity) && !$entity->get('base');
    }
}
