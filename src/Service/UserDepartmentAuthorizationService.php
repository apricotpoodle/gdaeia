<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\User;
use App\Model\Entity\UserDepartment;
use Cake\Http\Exception\ForbiddenException;
use Cake\ORM\TableRegistry;

/** Centralise le contrôle serveur des affectations départementales utilisateur. */
final class UserDepartmentAuthorizationService
{
    /**
     * Vérifie qu'une mutation conserve toutes les associations dans le périmètre de l'opérateur.
     *
     * @param \App\Model\Entity\User $operator Opérateur courant.
     * @param \App\Model\Entity\User $before État persistant avant mutation.
     * @param \App\Model\Entity\User $after État hydraté après mutation.
     * @return void
     */
    public function assertMutationAllowed(User $operator, User $before, User $after): void
    {
        $departmentIds = array_values(array_unique(array_merge(
            $this->departmentIds($before),
            $this->departmentIds($after),
        )));

        $this->assertDepartmentIdsAllowed($operator, $departmentIds);
    }

    /**
     * Vérifie une liste de départements explicitement ciblés par une mutation.
     *
     * @param \App\Model\Entity\User $operator Opérateur courant.
     * @param list<int> $departmentIds Identifiants explicites ciblés.
     * @return void
     */
    public function assertDepartmentIdsAllowed(User $operator, array $departmentIds): void
    {
        if ($operator->isSuperUser() || $departmentIds === []) {
            return;
        }

        $departmentIds = array_values(array_unique(array_map('intval', $departmentIds)));
        $departments = TableRegistry::getTableLocator()->get('Departments');
        $allowedCount = $departments->find('visibleTo', user: $operator)
            ->where(['Departments.id IN' => $departmentIds])
            ->count();

        if ($allowedCount !== count($departmentIds)) {
            throw new ForbiddenException(
                __('Au moins un département ciblé est hors de votre périmètre.'),
            );
        }
    }

    /**
     * Retourne les associations départementales explicites d'un utilisateur.
     *
     * @param \App\Model\Entity\User $user Utilisateur inspecté.
     * @return list<int>
     */
    private function departmentIds(User $user): array
    {
        $associations = $user->get(User::FIELD_USER_DEPARTMENTS) ?? [];
        if (!is_iterable($associations)) {
            return [];
        }

        $departmentIds = [];
        foreach ($associations as $association) {
            if ($association instanceof UserDepartment) {
                $departmentId = $association->get(UserDepartment::FIELD_DEPARTMENT_ID);
            } elseif (is_array($association)) {
                $departmentId = $association[UserDepartment::FIELD_DEPARTMENT_ID] ?? null;
            } else {
                $departmentId = null;
            }

            if ($departmentId !== null && (int)$departmentId > 0) {
                $departmentIds[] = (int)$departmentId;
            }
        }

        return array_values(array_unique($departmentIds));
    }
}
