<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use Cake\ORM\TableRegistry;

/**
 * @class MenusController
 * @description Contrôleur d'API distribuant l'arborescence filtrée selon les rôles.
 * Compatible PHPStan Niveau 8+.
 * * @property \App\Model\Table\MenusTable $Menus
 */
class MenusController extends AppController
{
    /**
     * Initialisation du contrôleur d'API.
     * Configure le moteur de rendu pour produire exclusivement du JSON.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setClassName('Json');
    }

    /**
     * Événement de cycle de vie exécuté avant le routage de l'action.
     * Exempte l'action d'autorisation d'infrastructure globale.
     *
     * @param \Cake\Event\EventInterface $event L'événement en cours.
     * @return \Cake\Http\Response|null|void
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authorization->skipAuthorization(['index']);
    }

    /**
     * Action Index : GET /api/menus.json
     * Analyse l'identité de l'opérateur et extrait l'arbre hiérarchique éligible.
     *
     * @return void
     */
    public function index(): void
    {
        $this->request->allowMethod(['get']);

        /** @var \App\Model\Entity\User|null $user */
        $user = $this->getRequest()->getAttribute('identity')?->getOriginalData();

        /** @var \Cake\ORM\Query\SelectQuery $query */
        $query = $this->Menus->find('threaded')
            ->where(['Menus.active' => true])
            ->orderBy(['Menus.lft' => 'ASC']);

        if ($user === null) {
            $query->where(['1 = 0']);
        } else {
            /** @var bool $issuperuser */
            $issuperuser = $user->get('issuperuser') ?? false;

            if (!$issuperuser) {
                /** @var int|null $roleId */
                $roleId = $user->get('role_id');
                if ($roleId !== null) {
                    $roleMenusTable = TableRegistry::getTableLocator()->get('RoleMenus');

                    /** @var \Cake\ORM\Query\SelectQuery $allowedMenuIdsQuery */
                    $allowedMenuIdsQuery = $roleMenusTable->find()
                        ->select(['menu_id'])
                        ->where(['role_id' => $roleId]);

                    $query->where(['Menus.id IN' => $allowedMenuIdsQuery]);
                } else {
                    $query->where(['1 = 0']);
                }
            }
        }

        $menus = $query->all();

        /** @var array<string, mixed>|null $userData */
        $userData = null;

        if ($user !== null) {
            try {
                $userTable = TableRegistry::getTableLocator()->get('Users');

                /** @var \App\Model\Entity\User $userWithRole */
                $userWithRole = $userTable->get($user->get('id'), [
                    'contain' => ['Roles']
                ]);

                $userData = [
                    'email' => $userWithRole->get('email'),
                    'role_name' => $userWithRole->role ? $userWithRole->role->get('name') : 'Sans Rôle',
                    'issuperuser' => (bool)$userWithRole->get('issuperuser'),
                    'is_impersonated' => $this->Authentication->isImpersonating(),
                ];
            } catch (\Throwable $th) {
                $userData = [
                    'email' => $user->get('email'),
                    'role_name' => 'Utilisateur',
                    'issuperuser' => (bool)$user->get('issuperuser'),
                    'is_impersonated' => $this->Authentication->isImpersonating(),
                ];
            }
        }

        $this->set(compact('menus', 'userData'));
        $this->viewBuilder()->setOption('serialize', ['menus', 'userData']);
    }
}
