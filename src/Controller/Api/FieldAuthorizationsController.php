<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use App\Model\Entity\AuthorizationTarget;
use App\Model\Entity\FieldAuthorization;
use App\Service\DataGrid\TabulatorAdapter;
use App\Service\Metadata\FieldMetadataService;
use App\Service\Security\FieldAuthorizationService;
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
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->Authorization->skipAuthorization();
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
            'page' => (int)($queryParams['page'] ?? 1),
            'sortableFields' => [],
        ]);

        // 5. Injection des droits de grille (cellules / lignes)
        $rightsFormatter = $this->createGridRightsFormatter();

        // 6. Formatage de la réponse
        $output = $adapter->adaptResponse($paginatedData, $rightsFormatter);
        $metadata = new FieldMetadataService();
        $output['data'] = array_map(
            static function ($authorization) use ($metadata): array {
                $data = $authorization->toArray();
                $data[FieldAuthorization::FIELD_ACCESS_LEVEL] = FieldAuthorization::normalizeAccessLevel(
                    (string)$authorization->get(FieldAuthorization::FIELD_ACCESS_LEVEL),
                );
                $resource = (string)$authorization->get(FieldAuthorization::FIELD_RESOURCE);
                $target = (string)$authorization->get(FieldAuthorization::FIELD_TARGET);
                $type = (string)$authorization->get(FieldAuthorization::FIELD_TARGET_TYPE);
                $data['target_label'] = $metadata->targetLabel($resource, $type, $target);
                $data['target_description'] = $metadata->targetDescription($resource, $type, $target);
                // Compatibilité avec les consommateurs historiques de la grille.
                $data['field'] = $target;
                $data['field_label'] = $data['target_label'];
                $data['field_description'] = $data['target_description'];

                return $data;
            },
            $output['data'],
        );

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
        $this->Authorization->authorize($this->FieldAuthorizations->newEmptyEntity(), 'index');

        $service = new FieldAuthorizationService();
        $identity = $this->request->getAttribute('identity');

        $schema = $service->getFieldSchema($identity, 'FieldAuthorizations');

        $rolesTable = TableRegistry::getTableLocator()->get('Roles');
        $roles = $rolesTable->find('list', keyField: 'id', valueField: 'name')->toArray();
        $currentResource = (string)$this->request->getQuery('current_resource');
        $currentTarget = (string)$this->request->getQuery('current_target');
        $fields = TableRegistry::getTableLocator()->get('FieldDefinitions')->find()
            ->where(['OR' => [
                ['active' => true],
                ['resource' => $currentResource, 'field' => $currentTarget, 'active' => false],
            ]])
            ->orderBy(['resource' => 'ASC', 'position' => 'ASC'])
            ->all()->toArray();
        $sections = TableRegistry::getTableLocator()->get('AuthorizationTargets')->find()
            ->where(['target_type' => AuthorizationTarget::TYPE_SECTION, 'OR' => [
                ['active' => true],
                ['resource' => $currentResource, 'target' => $currentTarget, 'active' => false],
            ]])
            ->orderBy(['resource' => 'ASC', 'position' => 'ASC'])
            ->all()->toArray();
        $resources = array_values(array_unique(array_merge(
            array_map(static fn($item): string => (string)$item->get('resource'), $fields),
            array_map(static fn($item): string => (string)$item->get('resource'), $sections),
        )));
        $targets = array_merge(
            array_map(static fn($item): array => [
                'resource' => $item->get('resource'), 'target_type' => 'field',
                'target' => $item->get('field'), 'label' => $item->get('label'),
                'description' => $item->get('description'),
            ], $fields),
            array_map(static fn($item): array => [
                'resource' => $item->get('resource'), 'target_type' => 'section',
                'target' => $item->get('target'), 'label' => $item->get('label'),
                'description' => $item->get('description'),
            ], $sections),
        );

        $this->set(compact('schema', 'roles', 'resources', 'targets'));
        $this->viewBuilder()->setOption('serialize', ['schema', 'roles', 'resources', 'targets']);
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

        // 2. Filtration des données soumises selon le schéma de rôle
        $filteredData = $this->authorizedRequestData('FieldAuthorizations');

        $fieldAuthorization = $this->FieldAuthorizations->patchEntity($fieldAuthorization, $filteredData);

        if ($this->FieldAuthorizations->save($fieldAuthorization)) {
            return $this->response->withType('application/json')
                ->withStringBody((string)json_encode([
                    'success' => true,
                    'message' => __('La règle d’autorisation a été créée avec succès.'),
                    'errors' => null,
            'id' => $fieldAuthorization->get(FieldAuthorization::FIELD_ID),
                ], JSON_UNESCAPED_UNICODE));
        }

        return $this->validationErrorResponse($fieldAuthorization, 'FieldAuthorizations');
    }

    /**
     * Endpoint : PUT/PATCH /api/field-authorizations/edit/{id}.json
     */
    public function edit(string $id): ?Response
    {
        $this->request->allowMethod(['post', 'put', 'patch']);

        $fieldAuthorization = $this->FieldAuthorizations->get($id);
        $this->Authorization->authorize($fieldAuthorization, 'edit');

        $filteredData = $this->authorizedRequestData('FieldAuthorizations');

        $fieldAuthorization = $this->FieldAuthorizations->patchEntity($fieldAuthorization, $filteredData);

        if ($this->FieldAuthorizations->save($fieldAuthorization)) {
            $fieldAuthorization = $this->FieldAuthorizations->get(
                $fieldAuthorization->get('id'),
                contain: ['Roles'],
            );

            return $this->response->withType('application/json')
                ->withStringBody((string)json_encode([
                    'success' => true,
                    'message' => __('La règle d’autorisation a été modifiée avec succès.'),
                    'errors' => null,
                    'data' => $fieldAuthorization->toArray(),
                ], JSON_UNESCAPED_UNICODE));
        }

        return $this->validationErrorResponse($fieldAuthorization, 'FieldAuthorizations');
    }
}
