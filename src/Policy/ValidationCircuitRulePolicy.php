<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\ValidationCircuitRule;
use Authorization\IdentityInterface;
use Cake\ORM\TableRegistry;

/** Autorise la gestion des règles dans le périmètre de l'opérateur. */
final class ValidationCircuitRulePolicy extends AppPolicy
{
    /** Vérifie l'accès à la page et à la liste des règles. */
    public function canIndex(IdentityInterface $identity, ValidationCircuitRule $rule): bool
    {
        return $this->canManage($identity, $rule);
    }

    /** Vérifie l'accès à une règle du périmètre de l'opérateur. */
    public function canManage(IdentityInterface $identity, ValidationCircuitRule $rule): bool
    {
        $user = $this->getValidUser($identity);
        if ($user === null) {
            return false;
        }
        if ($user->isSuperUser()) {
            return true;
        }
        if ((int)$user->get('id') < 1 || (int)$user->get('role_id') < 1) {
            return false;
        }
        $query = TableRegistry::getTableLocator()->get('Departments')
            ->find('circuitManageableTo', user: $user);
        $departmentId = $rule->get(ValidationCircuitRule::FIELD_DEPARTMENT_ID);
        if ($departmentId !== null) {
            $query->where(['Departments.id' => $departmentId]);
        }

        return $query->count() > 0;
    }

    /** Autorise la création sur le même périmètre que la gestion. */
    public function canAdd(IdentityInterface $identity, ValidationCircuitRule $rule): bool
    {
        return $this->canManage($identity, $rule);
    }

    /** Autorise la lecture d'une règle affichée par la grille. */
    public function canView(IdentityInterface $identity, ValidationCircuitRule $rule): bool
    {
        return $this->canManage($identity, $rule);
    }

    /** Autorise l'édition sur le même périmètre que la gestion. */
    public function canEdit(IdentityInterface $identity, ValidationCircuitRule $rule): bool
    {
        return $this->canManage($identity, $rule);
    }

    /** Autorise le retrait sur le même périmètre que la gestion. */
    public function canDelete(IdentityInterface $identity, ValidationCircuitRule $rule): bool
    {
        return $this->canManage($identity, $rule);
    }

    /** Réserve la désignation des gestionnaires aux super-administrateurs. */
    public function canManageGrants(IdentityInterface $identity, ValidationCircuitRule $rule): bool
    {
        return $this->isSuperUser($identity);
    }
}
