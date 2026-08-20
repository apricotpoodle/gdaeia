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
 * Class FieldAuthorizationsController (API)
 *
 * Contrôleur dédié à l'exposition des autorisations de champs au format JSON.
 *
 * @package App\Controller\Api
 * @property \App\Model\Table\FieldAuthorizationsTable $FieldAuthorizations
 */
class FieldAuthorizationsController extends AppController
{
    /**
     * Initialisation du contrôleur.
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setClassName('Json');
    }

    /**
     * Bypass propre du middleware strict pour l'action index
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authorization->skipAuthorization(['index']);
    }

    /**
     * Méthode Index (GET /api/field-authorizations.json)
     */
    public function index(): void
    {
        $this->request->allowMethod(['get']);

        // 1. Verrou de sécurité via la Policy
        $this->Authorization->authorize($this->FieldAuthorizations->newEmptyEntity(), 'index');

        $adapter = new TabulatorAdapter();
        $queryParams = $this->request->getQueryParams();

        // 2. Préparation de la requête ORM
        $query = $this->FieldAuthorizations->find()
            ->contain(['Roles']);

        // 3. Traduction des filtres/tris Tabulator
        $query = $adapter->adaptRequest($this->request, $query);

        // 4. Pagination
        $paginatedData = $this->paginate($query, [
            'limit' => (int)($queryParams['size'] ?? 20),
            'page'  => (int)($queryParams['page'] ?? 1),
            'sortableFields' => [],
        ]);

        // 5. Injection des droits de grille (cellules / lignes)
        $rightsFormatter = $this->createGridRightsFormatter();

        // 6. Formatage de la réponse
        $output = $adapter->adaptResponse($paginatedData, $rightsFormatter);

        // 7. Sérialisation JSON
        $this->set($output);
        $this->viewBuilder()->setOption('serialize', array_keys($output));
    }

    /**
     * Endpoint : GET /api/field-authorizations/get-form-schema.json
     * Distribue le schéma de formulaire pour autoriser/masquer les champs et le bouton Add
     */
    public function getFormSchema(): void
    {
        $this->request->allowMethod(['get']);
        $this->Authorization->skipAuthorization();

        $service = new FieldAuthorizationService();
        $identity = $this->request->getAttribute('identity');

        $schema = $service->getFieldSchema($identity, 'FieldAuthorizations');

        $rolesTable = TableRegistry::getTableLocator()->get('Roles');
        $roles = $rolesTable->find('list', keyField: 'id', valueField: 'name')->toArray();

        $this->set(compact('schema', 'roles'));
        $this->viewBuilder()->setOption('serialize', ['schema', 'roles']);
    }

    /**
     * Endpoint : POST /api/field-authorizations/add.json
     */
    public function add(): ?Response
    {
        $this->request->allowMethod(['post']);

        // 1. Verrou global de création
        $this->Authorization->authorize($this->FieldAuthorizations->newEmptyEntity(), 'add');

        $fieldAuthorization = $this->FieldAuthorizations->newEmptyEntity();

        $authService = new FieldAuthorizationService();
        $identity = $this->request->getAttribute('identity');

        // 2. Filtration des données soumises selon le schéma de rôle
        $schema = $authService->getFieldSchema($identity, 'FieldAuthorizations');
        $filteredData = $authService->filterRequestData($this->request->getData(), $schema);

        $fieldAuthorization = $this->FieldAuthorizations->patchEntity($fieldAuthorization, $filteredData);

        if ($this->FieldAuthorizations->save($fieldAuthorization)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => true]));
        }

        return $this->handleValidationError($fieldAuthorization);
    }

    /**
     * Endpoint : PUT/PATCH /api/field-authorizations/edit/{id}.json
     */
    public function edit(string $id): ?Response
    {
        $this->request->allowMethod(['post', 'put', 'patch']);

        $fieldAuthorization = $this->FieldAuthorizations->get($id);
        $this->Authorization->authorize($fieldAuthorization, 'edit');

        $authService = new FieldAuthorizationService();
        $identity = $this->request->getAttribute('identity');

        $schema = $authService->getFieldSchema($identity, 'FieldAuthorizations');
        $filteredData = $authService->filterRequestData($this->request->getData(), $schema);

        $fieldAuthorization = $this->FieldAuthorizations->patchEntity($fieldAuthorization, $filteredData);

        if ($this->FieldAuthorizations->save($fieldAuthorization)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => true]));
        }

        return $this->handleValidationError($fieldAuthorization);
    }

    /**
     * Gestion centralisée des erreurs de validation
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
