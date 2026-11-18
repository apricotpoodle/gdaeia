<?php
declare(strict_types=1);

namespace App\Policy;

use Authorization\IdentityInterface;
use Cake\Datasource\EntityInterface;

/** Politique commune aux nomenclatures administrables. */
abstract class ReferencePolicy extends AdministrationPolicy
{
    /** Autorise l’accès à la grille. */
    public function canIndex(IdentityInterface $identity): bool
    {
        return $this->allowsAdministration($identity, 'references.read');
    }

    /** Autorise la consultation d’une ligne. */
    public function canView(IdentityInterface $identity, EntityInterface $entity): bool
    {
        return $this->allowsAdministration($identity, 'references.read');
    }

    /** Autorise la création d’une ligne. */
    public function canAdd(IdentityInterface $identity, EntityInterface $entity): bool
    {
        return $this->allowsAdministration($identity, 'references.create');
    }

    /** Vérifie la permission de modification, y compris pour un socle. */
    public function canEdit(IdentityInterface $identity, EntityInterface $entity): bool
    {
        return $this->allowsAdministration($identity, 'references.update');
    }

    /** Interdit la suppression des lignes socles. */
    public function canDelete(IdentityInterface $identity, EntityInterface $entity): bool
    {
        return $this->allowsAdministration($identity, 'references.delete') && !$entity->get('base');
    }
}
