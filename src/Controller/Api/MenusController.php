<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use App\Model\Entity\User;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;
use RuntimeException;
use Throwable;

/**
 * @class MenusController
 * @description Contrôleur d'API distribuant l'arborescence filtrée selon les rôles.
 * Compatible PHPStan Niveau 8+.
 * @property \App\Model\Table\MenusTable $Menus
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

    /** @inheritDoc */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        if ($this->getRequest()->getParam('action') === 'index') {
            $this->Authorization->skipAuthorization();
        }
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
        /** @var iterable<int, \App\Model\Entity\Menu> $menus */
        $data = $this->formatMenuTreeWithRights($menus, $rightsFormatter);

        $this->set([
            'data' => $data,
        ]);
        $this->viewBuilder()->setOption('serialize', ['data']);
    }

    /**
     * Retourne l'arbre des options actuellement attribuables par l'opérateur.
     *
     * @return void
     */
    public function roleAccessTree(): void
    {
        $this->request->allowMethod(['get']);
        $this->Authorization->authorize($this->Menus->newEmptyEntity(), 'roleAccess');

        $menus = $this->Menus->find('roleAccessVisibleTo', user: $this->getRoleAccessOperator())
            ->find('threaded')
            ->orderBy(['Menus.lft' => 'ASC'])
            ->all();

        $this->set('data', $menus);
        $this->viewBuilder()->setOption('serialize', ['data']);
    }

    /**
     * Retourne les rôles que l'opérateur peut administrer.
     *
     * @return void
     */
    public function roleAccessRoles(): void
    {
        $this->request->allowMethod(['get']);
        $this->Authorization->authorize($this->Menus->newEmptyEntity(), 'roleAccess');

        $roles = $this->fetchTable('Roles')->find('roleAccessVisibleTo', user: $this->getRoleAccessOperator())
            ->orderBy(['Roles.name' => 'ASC'])
            ->all();

        $this->set('data', $roles);
        $this->viewBuilder()->setOption('serialize', ['data']);
    }

    /**
     * Retourne les rôles associés à toutes les options sélectionnées.
     *
     * @return void
     */
    public function roleAccessAssignedRoles(): void
    {
        $this->request->allowMethod(['get']);
        $this->Authorization->authorize($this->Menus->newEmptyEntity(), 'roleAccess');

        $user = $this->getRoleAccessOperator();
        $menuIds = $this->resolveSelectedMenuIds($this->request->getQuery('menu_ids'), $user);
        $roleIds = $this->fetchTable('RoleMenus')->find(
            'roleIdsAssociatedWithMenus',
            menuIds: $menuIds,
            user: $user,
        );
        $roles = $this->fetchTable('Roles')->find('roleAccessVisibleTo', user: $user)
            ->where(['Roles.id IN' => $roleIds])
            ->orderBy(['Roles.name' => 'ASC'])
            ->all()
            ->toList();

        $this->set('data', $roles);
        $this->viewBuilder()->setOption('serialize', ['data']);
    }

    /**
     * Associe un rôle aux options sélectionnées et à tous leurs descendants.
     *
     * @return \Cake\Http\Response
     */
    public function assignRoleAccess(): Response
    {
        $this->request->allowMethod(['post']);
        $this->Authorization->authorize($this->Menus->newEmptyEntity(), 'roleAccess');

        $user = $this->getRoleAccessOperator();
        $roleId = $this->positiveInteger($this->request->getData('role_id'), 'role_id');
        $selectedMenuIds = $this->resolveSelectedMenuIds($this->request->getData('menu_ids'), $user);
        $menuIds = $this->resolveSelectedMenuAndDescendantIds($selectedMenuIds, $user);

        $roles = $this->fetchTable('Roles');
        if ($roles->find('roleAccessVisibleTo', user: $user)->where(['Roles.id' => $roleId])->count() === 0) {
            throw new NotFoundException(__('Rôle introuvable.'));
        }

        $roleMenus = $this->fetchTable('RoleMenus');
        $created = $roleMenus->getConnection()->transactional(function () use ($roleMenus, $roleId, $menuIds): int {
            $existingMenuIds = $roleMenus->find()
                ->select(['menu_id'])
                ->where([
                    'role_id' => $roleId,
                    'menu_id IN' => $menuIds,
                ])
                ->all()
                ->extract('menu_id')
                ->map(fn($id): int => (int)$id)
                ->toList();
            $missingMenuIds = array_values(array_diff($menuIds, $existingMenuIds));

            foreach ($missingMenuIds as $menuId) {
                $roleMenu = $roleMenus->newEntity([
                    'role_id' => $roleId,
                    'menu_id' => $menuId,
                    'department_id' => null,
                ]);
                if (!$roleMenus->save($roleMenu)) {
                    throw new RuntimeException(__('Impossible d’enregistrer une association de rôle.'));
                }
            }

            return count($missingMenuIds);
        });

        return $this->jsonSuccess(['associations_created' => $created]);
    }

    /**
     * Retire toutes les associations du rôle pour les options sélectionnées.
     *
     * @return \Cake\Http\Response
     */
    public function unassignRoleAccess(): Response
    {
        $this->request->allowMethod(['post']);
        $this->Authorization->authorize($this->Menus->newEmptyEntity(), 'roleAccess');

        $user = $this->getRoleAccessOperator();
        $roleId = $this->positiveInteger($this->request->getData('role_id'), 'role_id');
        $selectedMenuIds = $this->resolveSelectedMenuIds($this->request->getData('menu_ids'), $user);
        $menuIds = $this->resolveSelectedMenuAndDescendantIds($selectedMenuIds, $user);

        $roleIsVisible = $this->fetchTable('Roles')->find('roleAccessVisibleTo', user: $user)
            ->where(['Roles.id' => $roleId])
            ->count() > 0;
        if (!$roleIsVisible) {
            throw new NotFoundException(__('Rôle introuvable.'));
        }

        $roleMenus = $this->fetchTable('RoleMenus');
        $deleted = $roleMenus->deleteAll([
            'role_id' => $roleId,
            'menu_id IN' => $menuIds,
        ]);

        return $this->jsonSuccess(['associations_deleted' => $deleted]);
    }

    /**
     * Valide les options actives visibles sélectionnées dans cet écran.
     *
     * @param mixed $rawMenuIds Valeur de requête ou de corps JSON.
     * @param \App\Model\Entity\User $user Opérateur connecté.
     * @return array<int>
     */
    private function resolveSelectedMenuIds(mixed $rawMenuIds, User $user): array
    {
        $menuIds = $this->positiveIntegerList($rawMenuIds, 'menu_ids');
        $visibleCount = $this->Menus->find('roleAccessVisibleTo', user: $user)
            ->where(['Menus.id IN' => $menuIds])
            ->count();
        if ($visibleCount !== count($menuIds)) {
            throw new NotFoundException(__('Une option de menu est introuvable ou inactive.'));
        }

        return $menuIds;
    }

    /**
     * Étend les options déjà validées à leurs descendants actifs visibles.
     *
     * @param array<int> $selectedMenuIds Options explicitement sélectionnées.
     * @param \App\Model\Entity\User $user Opérateur connecté.
     * @return array<int>
     */
    private function resolveSelectedMenuAndDescendantIds(array $selectedMenuIds, User $user): array
    {
        $selectedMenus = $this->Menus->find('roleAccessVisibleTo', user: $user)
            ->select(['id', 'lft', 'rght'])
            ->where(['Menus.id IN' => $selectedMenuIds])
            ->all()
            ->toList();
        /** @var list<\App\Model\Entity\Menu> $selectedMenus */

        $allMenus = $this->Menus->find('roleAccessVisibleTo', user: $user)
            ->select(['id', 'lft', 'rght'])
            ->all()
            ->toList();
        /** @var list<\App\Model\Entity\Menu> $allMenus */
        $menuIds = [];
        foreach ($allMenus as $menu) {
            foreach ($selectedMenus as $selectedMenu) {
                if ($menu->lft >= $selectedMenu->lft && $menu->rght <= $selectedMenu->rght) {
                    $menuIds[] = (int)$menu->id;
                    break;
                }
            }
        }

        return array_values(array_unique($menuIds));
    }

    /**
     * Normalise une liste d'identifiants strictement positifs.
     *
     * @param mixed $values Valeur brute issue de la requête.
     * @param string $field Nom du champ, utilisé dans les erreurs.
     * @return array<int> Identifiants uniques validés.
     */
    private function positiveIntegerList(mixed $values, string $field): array
    {
        if (!is_array($values)) {
            throw new BadRequestException(__('{0} doit être une liste.', $field));
        }

        $ids = [];
        foreach ($values as $value) {
            $ids[] = $this->positiveInteger($value, $field);
        }

        return array_values(array_unique($ids));
    }

    /**
     * Valide un identifiant strictement positif.
     *
     * @param mixed $value Valeur brute issue de la requête.
     * @param string $field Nom du champ, utilisé dans les erreurs.
     * @return int Identifiant validé.
     */
    private function positiveInteger(mixed $value, string $field): int
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false || (int)$value <= 0) {
            throw new BadRequestException(__('{0} doit être un entier positif.', $field));
        }

        return (int)$value;
    }

    /**
     * Extrait l'opérateur authentifié pour les finders de visibilité.
     *
     * @return \App\Model\Entity\User Opérateur connecté.
     */
    private function getRoleAccessOperator(): User
    {
        $user = $this->request->getAttribute('identity')?->getOriginalData();
        if (!$user instanceof User) {
            throw new ForbiddenException(__('Opérateur invalide.'));
        }

        return $user;
    }

    /**
     * Produit une réponse JSON de succès homogène.
     *
     * @param array<string, int> $data Données métier à inclure dans la réponse.
     * @return \Cake\Http\Response Réponse JSON sérialisée.
     */
    private function jsonSuccess(array $data): Response
    {
        return $this->response->withType('application/json')
            ->withStringBody((string)json_encode(['success' => true] + $data));
    }

    /**
     * Parcourt l'arborescence pour injecter dynamiquement 'grid_rights'
     * et s'assurer que chaque nœud porte son ID.
     *
     * @param iterable<int, \App\Model\Entity\Menu> $menus
     * @param callable $rightsFormatter
     * @return list<\App\Model\Entity\Menu>
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

        /** @var \Cake\ORM\Query\SelectQuery<\App\Model\Entity\Menu> $query */
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

                    /** @var \Cake\ORM\Query\SelectQuery<\App\Model\Entity\RoleMenu> $allowedMenuIdsQuery */
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
                $userWithRole = $userTable->get($user->get('id'), contain: ['Roles']);

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
