<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use App\Service\DataGrid\TabulatorAdapter;
use Cake\Event\EventInterface;
use Cake\Http\Response;

class FieldAuthorizationsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setClassName('Json');
    }

    public function index(): void
    {
        $this->request->allowMethod(['get']);
        $this->Authorization->authorize($this->FieldAuthorizations->newEmptyEntity(), 'index');

        $adapter = new TabulatorAdapter();
        $queryParams = $this->request->getQueryParams();

        $query = $this->FieldAuthorizations->find()->contain(['Roles']);
        $query = $adapter->adaptRequest($this->request, $query);

        $paginatedData = $this->paginate($query, [
            'limit' => (int)($queryParams['size'] ?? 20),
            'page'  => (int)($queryParams['page'] ?? 1),
            'sortableFields' => [] 
        ]);

        $rightsFormatter = $this->createGridRightsFormatter();
        $output = $adapter->adaptResponse($paginatedData, $rightsFormatter);

        $this->set($output);
        $this->viewBuilder()->setOption('serialize', array_keys($output));
    }

    /**
     * Endpoint : POST /api/field-authorizations/add.json
     */
    public function add(): ?Response
    {
        $this->request->allowMethod(['post']);
        $this->Authorization->authorize($this->FieldAuthorizations->newEmptyEntity(), 'add');

        $fieldAuthorization = $this->FieldAuthorizations->newEmptyEntity();
        $fieldAuthorization = $this->FieldAuthorizations->patchEntity($fieldAuthorization, $this->request->getData());

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

        $fieldAuthorization = $this->FieldAuthorizations->patchEntity($fieldAuthorization, $this->request->getData());

        if ($this->FieldAuthorizations->save($fieldAuthorization)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => true]));
        }

        return $this->handleValidationError($fieldAuthorization);
    }

    /**
     * Extraction DRY de la gestion des erreurs de validation
     */
    private function handleValidationError(\Cake\Datasource\EntityInterface $entity): Response
    {
        $errors = $entity->getErrors();
        $message = __("Le formulaire contient des données invalides.");
        
        if (!empty($errors)) {
            $firstError = current(reset($errors));
            $message = (string)$firstError;
        }

        return $this->response->withType('application/json')
            ->withStatus(400)
            ->withStringBody(json_encode(['success' => false, 'message' => $message]));
    }
}