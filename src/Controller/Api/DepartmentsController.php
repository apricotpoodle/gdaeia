<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use App\Model\Entity\Department;
use App\Service\DataGrid\TabulatorAdapter;
use App\Service\DepartmentManagementService;
use Cake\Http\Response;
use DomainException;

/** API de gestion des départements, de leur arbre et de leurs codes CGR. */
final class DepartmentsController extends AppController
{
    /** Initialise le rendu JSON de l'API. */
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setClassName('Json');
    }

    /** Retourne les enfants directs d’un nœud pour le chargement lazy. */
    public function tree(): void
    {
        $this->request->allowMethod(['get']);
        $table = $this->fetchTable('Departments');
        $this->Authorization->authorize($table->newEmptyEntity(), 'index');
        $parentId = $this->request->getQuery('parent_id');
        $parentId = $parentId === null || $parentId === '' ? null : (int)$parentId;
        $service = new DepartmentManagementService();
        $rows = array_map(
            fn(Department $department): array => $this->departmentRow($department, $service),
            $service->getChildren($parentId),
        );
        $this->set(['success' => true, 'data' => $rows]);
        $this->viewBuilder()->setOption('serialize', ['success', 'data']);
    }

    /** Retourne la fiche d’un département actif. */
    public function view(string $id): void
    {
        $department = $this->activeDepartment($id);
        $this->Authorization->authorize($department, 'view');
        $this->set(['success' => true, 'data' => $department]);
        $this->viewBuilder()->setOption('serialize', ['success', 'data']);
    }

    /** Crée un département. */
    public function add(): Response
    {
        $this->request->allowMethod(['post']);
        $table = $this->fetchTable('Departments');
        /** @var \App\Model\Entity\Department $department */
        $department = $table->newEmptyEntity();
        $this->Authorization->authorize($department, 'add');

        try {
            $saved = (new DepartmentManagementService())->save($department, $this->request->getData());
        } catch (DomainException $exception) {
            return $this->domainError($exception);
        }
        if ($saved->getErrors() !== []) {
            return $this->validationErrorResponse($saved, 'Departments');
        }

        return $this->jsonSuccess(['id' => $saved->get('id'), 'data' => $saved], __('Le département a été créé.'));
    }

    /** Modifie un département actif. */
    public function edit(string $id): Response
    {
        $this->request->allowMethod(['post', 'put', 'patch']);
        $department = $this->activeDepartment($id);
        $this->Authorization->authorize($department, 'edit');

        try {
            $saved = (new DepartmentManagementService())->save($department, $this->request->getData());
        } catch (DomainException $exception) {
            return $this->domainError($exception);
        }
        if ($saved->getErrors() !== []) {
            return $this->validationErrorResponse($saved, 'Departments');
        }

        return $this->jsonSuccess(['data' => $saved], __('Le département a été mis à jour.'));
    }

    /** Retourne les impacts d’une désactivation ou d’une mutation structurelle. */
    public function impact(string $departmentId): void
    {
        $department = $this->activeDepartment($departmentId);
        $this->Authorization->authorize($department, 'viewImpacts');
        $this->set(['success' => true, 'data' => (new DepartmentManagementService())->getImpact($department)]);
        $this->viewBuilder()->setOption('serialize', ['success', 'data']);
    }

    /** Désactive logiquement un département. */
    public function deactivate(string $id): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $department = $this->activeDepartment($id);
        $this->Authorization->authorize($department, 'deactivate');
        try {
            (new DepartmentManagementService())->deactivate($department);
        } catch (DomainException $exception) {
            return $this->domainError($exception);
        }

        return $this->jsonSuccess(message: __('Le département a été désactivé.'));
    }

    /** Retourne les codes CGR actifs d’un département. */
    public function cgrCodes(string $departmentId): void
    {
        $department = $this->activeDepartment($departmentId);
        $this->Authorization->authorize($department, 'view');
        $table = $this->fetchTable('CgrCodes');
        $query = (new TabulatorAdapter())->adaptRequest($this->request, $table->find()->where([
            'department_id' => $department->get('id'),
            'active' => true,
        ]));
        $data = $this->paginate($query, [
            'limit' => (int)($this->request->getQuery('size') ?? 20),
            'page' => (int)($this->request->getQuery('page') ?? 1),
            'sortableFields' => ['type', 'code', 'label', 'active', 'is_system'],
        ]);
        $output = (new TabulatorAdapter())->adaptResponse($data, $this->createGridRightsFormatter(['deactivate']));
        $this->set($output);
        $this->viewBuilder()->setOption('serialize', array_keys($output));
    }

    /** Ajoute un code CGR. */
    public function addCgrCode(string $departmentId): Response
    {
        $department = $this->activeDepartment($departmentId);
        $table = $this->fetchTable('CgrCodes');
        $code = $table->newEntity(['department_id' => $department->get('id')]);
        $this->Authorization->authorize($code, 'add');
        $code = $table->patchEntity($code, $this->request->getData(), [
            'accessibleFields' => ['department_id' => false, 'is_system' => false],
        ]);
        if (!$table->save($code)) {
            return $this->validationErrorResponse($code, 'CgrCodes');
        }

        return $this->jsonSuccess(['id' => $code->get('id'), 'data' => $code], __('Le code CGR a été créé.'));
    }

    /** Modifie un code CGR. */
    public function editCgrCode(string $departmentId, string $id): Response
    {
        $this->activeDepartment($departmentId);
        $table = $this->fetchTable('CgrCodes');
        /** @var \App\Model\Entity\CgrCode $code */
        $code = $table->find()->where(['id' => $id, 'department_id' => $departmentId, 'active' => true])->firstOrFail();
        $this->Authorization->authorize($code, 'edit');
        $code = $table->patchEntity($code, $this->request->getData(), [
            'accessibleFields' => ['department_id' => false, 'type' => false, 'is_system' => false],
        ]);
        if (!$table->save($code)) {
            return $this->validationErrorResponse($code, 'CgrCodes');
        }

        return $this->jsonSuccess(['data' => $code], __('Le code CGR a été mis à jour.'));
    }

    /** Désactive un code CGR. */
    public function deactivateCgrCode(string $departmentId, string $id): Response
    {
        $this->activeDepartment($departmentId);
        $table = $this->fetchTable('CgrCodes');
        /** @var \App\Model\Entity\CgrCode $code */
        $code = $table->find()->where(['id' => $id, 'department_id' => $departmentId, 'active' => true])->firstOrFail();
        $this->Authorization->authorize($code, 'deactivate');
        $code->set('active', false);
        if (!$table->save($code)) {
            return $this->validationErrorResponse($code, 'CgrCodes');
        }

        return $this->jsonSuccess(message: __('Le code CGR a été désactivé.'));
    }

    /** Charge un département actif par son identifiant. */
    private function activeDepartment(string $id): Department
    {
        /** @var \App\Model\Entity\Department $department */
        $department = $this->fetchTable('Departments')->find()
            ->contain(['CgrStrategies', 'Managers'])
            ->where(['Departments.id' => $id, 'Departments.deleted IS' => null])
            ->firstOrFail();

        return $department;
    }

    /** @return array<string, mixed> */
    private function departmentRow(Department $department, DepartmentManagementService $service): array
    {
        return [
            'id' => (int)$department->get('id'),
            'parent_id' => $department->get('parent_id') === null ? null : (int)$department->get('parent_id'),
            'code' => $department->get('code'),
            'name' => $department->get('name'),
            'sort' => $department->get('sort'),
            'level' => (int)$department->get('level'),
            'department_type_id' => (int)$department->get('department_type_id'),
            'manager' => $department->get('manager')?->get('firstname') . ' '
                . $department->get('manager')?->get('lastname'),
            'cgr_strategy' => $department->get('cgr_strategy')?->get('name'),
            'default_cgr' => $department->get('default_cgr'),
            'has_children' => $service->hasChildren($department),
            'children' => $service->hasChildren($department)
                ? [[
                    'id' => 'loading-' . $department->get('id'),
                    '_tree_placeholder' => true,
                    'name' => __('Chargement…'),
                ]]
                : [],
            'grid_rights' => [
                'actions' => [
                    'view' => $this->Authorization->can($department, 'view'),
                    'edit' => $this->Authorization->can($department, 'edit'),
                    'deactivate' => $this->Authorization->can($department, 'deactivate'),
                ],
                'columns' => [],
            ],
        ];
    }

    /** @param array<string, mixed> $data */
    private function jsonSuccess(array $data = [], ?string $message = null): Response
    {
        return $this->response->withType('application/json')->withStringBody((string)json_encode([
            'success' => true,
            'message' => $message,
        ] + $data, JSON_UNESCAPED_UNICODE));
    }

    /** Transforme une règle métier refusée en réponse JSON HTTP 409. */
    private function domainError(DomainException $exception): Response
    {
        return $this->response->withType('application/json')->withStatus(409)
            ->withStringBody((string)json_encode([
                'success' => false,
                'message' => $exception->getMessage(),
                'errors' => null,
            ], JSON_UNESCAPED_UNICODE));
    }
}
