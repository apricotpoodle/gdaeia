<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\User;
use Cake\Http\Exception\ForbiddenException;

/**
 * Contrôle les transitions de rôle et de statut superuser des utilisateurs.
 */
final class UserRoleAuthorizationService
{
    /**
     * Retourne les rôles qu'un opérateur peut proposer dans un formulaire.
     *
     * @param \App\Model\Entity\User $operator Opérateur courant.
     * @return list<int>|null Tous les rôles si l'opérateur est superuser.
     */
    public function allowedRoleIds(User $operator): ?array
    {
        if ($operator->isSuperUser()) {
            return null;
        }

        return User::FUNCTIONAL_ROLE_IDS;
    }

    /**
     * Refuse les transitions privilégiées pour un opérateur local.
     *
     * @param \App\Model\Entity\User $operator Opérateur courant.
     * @param \App\Model\Entity\User $before État persistant avant mutation.
     * @param \App\Model\Entity\User $after État hydraté après mutation.
     * @return void
     * @throws \Cake\Http\Exception\ForbiddenException Si la transition est interdite.
     */
    public function assertMutationAllowed(User $operator, User $before, User $after): void
    {
        if ($operator->isSuperUser()) {
            return;
        }

        $beforeRoleId = $before->get(User::FIELD_ROLE_ID);
        $afterRoleId = $after->get(User::FIELD_ROLE_ID);
        if ($beforeRoleId !== $afterRoleId && !in_array((int)$afterRoleId, User::FUNCTIONAL_ROLE_IDS, true)) {
            throw new ForbiddenException(__('Un administrateur local ne peut pas attribuer ce rôle.'));
        }

        if ((bool)$before->get(User::FIELD_ISSUPERUSER) !== (bool)$after->get(User::FIELD_ISSUPERUSER)) {
            throw new ForbiddenException(__(
                'Seul un super administrateur peut modifier le statut superuser.',
            ));
        }
    }
}
