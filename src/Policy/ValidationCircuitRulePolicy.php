<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\User;
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
        if ($user === null || $user->isSuperUser() || $user->hasRole(User::ROLE_ADMIN)) {
            return $user !== null && ($user->isSuperUser() || $user->hasRole(User::ROLE_ADMIN));
        }
        if (!in_array((int)$user->role_id, [User::ROLE_4_VALIDEUR_CG, User::ROLE_5_VALIDEUR_DIR], true)) {
            return false;
        }
        if ($rule->get(ValidationCircuitRule::FIELD_DEPARTMENT_ID) === null) {
            return TableRegistry::getTableLocator()->get('UserDepartments')->find()
                ->where(['user_id' => $user->id])->count() > 0;
        }

        return TableRegistry::getTableLocator()->get('UserDepartments')->find()
            ->where([
                'user_id' => $user->id,
                'department_id' => $rule->get(ValidationCircuitRule::FIELD_DEPARTMENT_ID),
            ])
            ->count() > 0;
    }
}
