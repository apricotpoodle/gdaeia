<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\Role;

/** Écran d’audit du catalogue des permissions. */
final class PermissionsController extends AppController
{
    /** Affiche le sélecteur des rôles actifs et la grille du catalogue. */
    public function index(): void
    {
        $permission = $this->fetchTable('Permissions')->newEmptyEntity();
        $this->Authorization->authorize($permission, 'index');

        /** @var \App\Model\Entity\User $user */
        $user = $this->request->getAttribute('identity')->getOriginalData();
        $roles = $this->fetchTable('Roles')->find('visibleList', user: $user)
            ->orderByAsc('Roles.' . Role::FIELD_NAME)
            ->toArray();
        $this->set(compact('roles'));
    }
}
