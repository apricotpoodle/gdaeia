<?php
declare(strict_types=1);

namespace App\Policy;

use App\Service\Security\PermissionResolver;
use Authorization\IdentityInterface;
use Cake\Datasource\EntityInterface;

/** Autorisations de l’administration de l’arbre des départements. */
final class DepartmentPolicy extends AppPolicy
{
    /** Construit la policy avec son résolveur de permissions. */
    public function __construct(private readonly PermissionResolver $permissions = new PermissionResolver())
    {
    }

    /** Autorise l’accès à l’index des départements. */
    public function canIndex(IdentityInterface $identity): bool
    {
        return $this->canReadDepartments($identity);
    }

    /** Autorise la consultation d’un département actif. */
    public function canView(IdentityInterface $identity, EntityInterface $department): bool
    {
        return $this->canReadDepartments($identity, $department) && $department->get('deleted') === null;
    }

    /** Autorise la création d’un département. */
    public function canAdd(IdentityInterface $identity, EntityInterface $department): bool
    {
        return $this->canManageDepartments($identity);
    }

    /** Autorise la modification d’un département actif. */
    public function canEdit(IdentityInterface $identity, EntityInterface $department): bool
    {
        return $this->canManageDepartments($identity, $department) && $department->get('deleted') === null;
    }

    /** Autorise la consultation des impacts d’un département actif. */
    public function canViewImpacts(IdentityInterface $identity, EntityInterface $department): bool
    {
        return $this->canManageDepartments($identity, $department) && $department->get('deleted') === null;
    }

    /** Autorise la consultation selon la permission et son périmètre. */
    protected function canReadDepartments(IdentityInterface $identity, ?EntityInterface $department = null): bool
    {
        $user = $this->getValidUser($identity);
        if ($user === null) {
            return false;
        }

        $departmentId = $department?->get('id');

        return $this->permissions->allowsDepartment(
            $user,
            'departments.read',
            is_int($departmentId) || ctype_digit((string)$departmentId) ? (int)$departmentId : null,
        );
    }

    /** Autorise la désactivation d’un département actif non socle. */
    public function canDeactivate(IdentityInterface $identity, EntityInterface $department): bool
    {
        return $this->canManageDepartments($identity, $department)
            && $department->get('deleted') === null
            && !$department->get('base');
    }

    /** Point d’extension pour élargir ultérieurement le périmètre des opérateurs. */
    protected function canManageDepartments(IdentityInterface $identity, ?EntityInterface $department = null): bool
    {
        $user = $this->getValidUser($identity);
        if ($user === null) {
            return false;
        }

        $departmentId = $department?->get('id');

        return $this->permissions->allowsDepartment(
            $user,
            'departments.manage',
            is_int($departmentId) || ctype_digit((string)$departmentId) ? (int)$departmentId : null,
        );
    }
}
