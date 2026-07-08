<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use Cake\Event\EventInterface;

/**
 * @class MenusController
 * @description Contrôleur d'API distribuant l'arborescence filtrée selon les rôles.
 * @property \App\Model\Table\MenusTable $Menus
 */
class MenusController extends AppController
{
    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setClassName('Json');
    }

    /**
     * Action Index : GET /api/menus.json
     * Filtre les options de menus en fonction des habilitations et rôles.
     *
     * @return void
     */
    public function index(): void
    {
        $this->request->allowMethod(['get']);

        // 1. Récupération de l'identité de l'utilisateur connecté
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->getRequest()->getAttribute('identity')?->getOriginalData();

        // Initialisation de la requête de base
        $query = $this->Menus->find('threaded')
            ->where(['Menus.active' => true])
            ->orderBy(['Menus.lft' => 'ASC']);

        // 2. Application des verrous de rôles (Sauf si l'utilisateur est Super Admin)
        if ($user === null) {
            // Par sécurité (Fail-Closed), un utilisateur non connecté ne voit rien
            $query->where(['1 = 0']);
        } elseif (!$user->get('issuperuser')) {
            // L'utilisateur n'est pas Super Admin : Filtrage par jointure stricte sur ses privilèges de rôle
            $roleId = $user->get('role_id');
            $query->innerJoinWith('RoleMenus', function ($q) use ($roleId) {
                return $q->where(['RoleMenus.role_id' => $roleId]);
            });
        }

        $menus = $query->all();

        $this->set(compact('menus'));
        $this->viewBuilder()->setOption('serialize', ['menus']);
    }
}
