<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use Cake\ORM\TableRegistry;
use Throwable;

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
     * Endpoint dédié à la grille Tabulator pour le CRUD des Menus.
     * Accessible via GET /api/menus/grid.json
     *
     * @return void
     */
    public function grid(): void
    {
        $this->request->allowMethod(['get']);
        // Verrou de sécurité calqué sur la Policy des Menus
        $this->Authorization->authorize($this->Menus->newEmptyEntity(), 'index');

        // 1. Récupération de l'arbre via le TreeBehavior de CakePHP
        $menus = $this->Menus->find('threaded')
            ->orderBy(['lft' => 'ASC'])
            ->all();

        // 2. Instanciation de notre usine à droits (DRY)
        $rightsFormatter = $this->createGridRightsFormatter(['moveUp', 'moveDown']);

        // 3. Application récursive des droits pour la vue en arbre de Tabulator
        $data = $this->formatMenuTreeWithRights($menus, $rightsFormatter);

        $this->set([
            'data' => $data,
        ]);
        $this->viewBuilder()->setOption('serialize', ['data']);
    }

    /**
     * Parcourt l'arborescence pour injecter dynamiquement 'grid_rights'
     * et s'assurer que chaque nœud porte son ID.
     *
     * @param iterable $menus
     * @param callable $rightsFormatter
     * @return array
     */
    private function formatMenuTreeWithRights(iterable $menus, callable $rightsFormatter): array
    {
        $result = [];
        foreach ($menus as $menu) {
            // Application des droits dynamiques
            $menu->grid_rights = $rightsFormatter($menu);

            // Traitement récursif des enfants (TreeBehavior 'children')
            if (!empty($menu->children)) {
                $menu->children = $this->formatMenuTreeWithRights($menu->children, $rightsFormatter);
            }

            $result[] = $menu;
        }

        return $result;
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
                    'contain' => ['Roles'],
                ]);

                $userData = [
                    'email' => $userWithRole->get('email'),
                    'role_name' => $userWithRole->role ? $userWithRole->role->get('name') : 'Sans Rôle',
                    'issuperuser' => (bool)$userWithRole->get('issuperuser'),
                    'is_impersonated' => $this->Authentication->isImpersonating(),
                ];
            } catch (Throwable $th) {
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
