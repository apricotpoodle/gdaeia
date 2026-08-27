<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use App\Service\DataGrid\TabulatorAdapter;
use App\Service\Security\FieldAuthorizationService;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;

/**
 * Class UsersController (API)
 *
 * Contrôleur dédié à l'exposition des données Utilisateurs au format JSON.
 *
 * @package App\Controller\Api
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    /**
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setClassName('Json');
    }

    /**
     * @param \Cake\Event\EventInterface $event
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->Authorization->skipAuthorization(['getFormSchema']);
    }

    /**
     * Endpoint : GET /api/users/get-form-schema.json
     * Fournit les permissions sur les champs, les rôles, et l'arborescence des départements autorisés.
     *
     * @return void
     */
    public function getFormSchema(): void
    {
        $this->request->allowMethod(['get']);

        $service = new FieldAuthorizationService();
        $identity = $this->request->getAttribute('identity');

        /** @var \App\Model\Entity\User $currentUser */
        $currentUser = $identity->getOriginalData();

        // Schéma ACL des champs
        $schema = $service->getFieldSchema($identity, 'Users');

        // Liste des rôles
        $rolesTable = TableRegistry::getTableLocator()->get('Roles');
        $roles = $rolesTable->find('list', keyField: 'id', valueField: 'name')
            ->orderBy(['Roles.name' => 'ASC'])
            ->toArray();

        // Arborescence filtrée par périmètre opérateur
        $departmentsTable = TableRegistry::getTableLocator()->get('Departments');
        $departments = $departmentsTable->findTreeSelectFormat($currentUser);

        $this->set(compact('schema', 'roles', 'departments'));
        $this->viewBuilder()->setOption('serialize', ['schema', 'roles', 'departments']);
    }

    /**
     * Endpoint : POST /api/users/add.json
     * Traite l'ajout d'un utilisateur et gère l'association de ses départements.
     *
     * @return \Cake\Http\Response|null
     */
    public function add(): ?Response
    {
        $this->request->allowMethod(['post']);
        $this->Authorization->authorize($this->Users->newEmptyEntity(), 'add');

        $user = $this->Users->newEmptyEntity();

        $authService = new FieldAuthorizationService();
        $identity = $this->request->getAttribute('identity');

        $schema = $authService->getFieldSchema($identity, 'Users');

        // Autoriser la présence de l'association user_departments dans le filtre ACL
        $schema['user_departments'] = 'EDIT';

        $rawParams = $this->request->getData();
        $filteredData = $authService->filterRequestData($rawParams, $schema);

        // Intégration et association ORM des départements
        $user = $this->Users->patchEntity($user, $filteredData, [
            'associated' => ['UserDepartments'],
        ]);

        if ($this->Users->save($user)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => true, 'id' => $user->id]));
        }

        return $this->handleValidationError($user);
    }

    /**
     * Endpoint : PUT/PATCH /api/users/edit/{id}.json
     *
     * @param string $id
     * @return \Cake\Http\Response|null
     */
    public function edit(string $id): ?Response
    {
        $this->request->allowMethod(['post', 'put', 'patch']);

        $user = $this->Users->get($id, contain: ['UserDepartments']);
        $this->Authorization->authorize($user, 'edit');

        $authService = new FieldAuthorizationService();
        $identity = $this->request->getAttribute('identity');

        $schema = $authService->getFieldSchema($identity, 'Users');
        $schema['user_departments'] = 'EDIT';

        $filteredData = $authService->filterRequestData($this->request->getData(), $schema);

        $user = $this->Users->patchEntity($user, $filteredData, [
            'associated' => ['UserDepartments'],
        ]);

        if ($this->Users->save($user)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => true]));
        }

        return $this->handleValidationError($user);
    }

    /**
     * Méthode Index (GET /api/users.json)
     *
     * @return void
     */
    public function index(): void
    {
        $this->request->allowMethod(['get']);
        $this->Authorization->authorize($this->Users->newEmptyEntity(), 'index');

        $adapter = new TabulatorAdapter();
        $queryParams = $this->request->getQueryParams();

        /** @var \App\Model\Entity\User $currentUser */
        $currentUser = $this->request->getAttribute('identity')->getOriginalData();

        $query = $this->Users->find('visibleTo', user: $currentUser)
            ->contain(['Roles', 'UserDepartments' => ['Departments']]);

        $query = $adapter->adaptRequest($this->request, $query);

        try {
            $paginatedData = $this->paginate($query, [
                'limit' => (int)($queryParams['size'] ?? 40),
                'page'  => (int)($queryParams['page'] ?? 1),
            ]);
        } catch (\Cake\Http\Exception\NotFoundException $e) {
            $this->request = $this->request->withQueryParams(array_merge($queryParams, ['page' => 1]));
            $paginatedData = $this->paginate($query, [
                'limit' => (int)($queryParams['size'] ?? 40),
                'page'  => 1,
            ]);
        }
        // 2. Détermination dynamique des actions supplémentaires selon le mode d'impersonation
        $extraActions = [];
        if (!$this->Authentication->isImpersonating()) {
            $extraActions[] = 'impersonate';
        }
        $rightsFormatter = $this->createGridRightsFormatter($extraActions);
        $output = $adapter->adaptResponse($paginatedData, $rightsFormatter);

        $this->set($output);
        $this->viewBuilder()->setOption('serialize', array_keys($output));
    }

    /**
     * Gestion des erreurs de validation
     *
     * @param \Cake\Datasource\EntityInterface $entity
     * @return \Cake\Http\Response
     */
    private function handleValidationError(EntityInterface $entity): Response
    {
        $errors = $entity->getErrors();
        $message = __('Le formulaire contient des données invalides.');

        if (!empty($errors)) {
            $firstError = current(reset($errors));
            $message = (string)$firstError;
        }

        return $this->response->withType('application/json')
            ->withStatus(400)
            ->withStringBody(json_encode(['success' => false, 'message' => $message]));
    }
}
