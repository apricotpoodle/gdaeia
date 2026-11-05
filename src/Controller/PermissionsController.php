<?php
declare(strict_types=1);

namespace App\Controller;

/** Écran d’audit du catalogue des permissions. */
final class PermissionsController extends AppController
{
    /** Affiche le catalogue et ses périmètres admissibles. */
    public function index(): void
    {
        $permission = $this->fetchTable('Permissions')->newEmptyEntity();
        $this->Authorization->authorize($permission, 'index');
        $permissions = $this->fetchTable('Permissions')->find()->contain(['PermissionScopes'])->all()->toList();
        $this->set(compact('permissions'));
    }
}
