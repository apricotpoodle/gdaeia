<?php
declare(strict_types=1);

namespace App\Policy;

use Authorization\IdentityInterface;
use Cake\Datasource\EntityInterface;

/** Autorisations de l’administration de l’arbre des départements. */
final class DepartmentPolicy extends AppPolicy
{
    /** Autorise l’accès à l’index des départements. */
    public function canIndex(IdentityInterface $identity): bool
    {
        return $this->canManageDepartments($identity);
    }

    /** Autorise la consultation d’un département actif. */
    public function canView(IdentityInterface $identity, EntityInterface $department): bool
    {
        return $this->canManageDepartments($identity) && $department->get('deleted') === null;
    }

    /** Autorise la création d’un département. */
    public function canAdd(IdentityInterface $identity, EntityInterface $department): bool
    {
        return $this->canManageDepartments($identity);
    }

    /** Autorise la modification d’un département actif. */
    public function canEdit(IdentityInterface $identity, EntityInterface $department): bool
    {
        return $this->canManageDepartments($identity) && $department->get('deleted') === null;
    }

    /** Autorise la consultation des impacts d’un département actif. */
    public function canViewImpacts(IdentityInterface $identity, EntityInterface $department): bool
    {
        return $this->canManageDepartments($identity) && $department->get('deleted') === null;
    }

    /** Autorise la désactivation d’un département actif non socle. */
    public function canDeactivate(IdentityInterface $identity, EntityInterface $department): bool
    {
        return $this->canManageDepartments($identity)
            && $department->get('deleted') === null
            && !$department->get('base');
    }

    /** Point d’extension pour élargir ultérieurement le périmètre des opérateurs. */
    protected function canManageDepartments(IdentityInterface $identity): bool
    {
        return $this->isSuperUser($identity);
    }
}
