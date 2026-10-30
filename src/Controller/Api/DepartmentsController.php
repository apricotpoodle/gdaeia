<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use App\Mailer\DepartmentDeactivationMailer;
use App\Model\Entity\Department;
use App\Model\Table\DepartmentsTable;
use App\Service\DataGrid\TabulatorAdapter;
use App\Service\DepartmentManagementService;
use Cake\Database\Expression\QueryExpression;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\ORM\Query\SelectQuery;
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
        /** @var \App\Model\Table\DepartmentsTable $table */
        $table = $this->fetchTable('Departments');
        $this->Authorization->authorize($table->newEmptyEntity(), 'index');
        $parentId = $this->request->getQuery('parent_id');
        $parentId = $parentId === null || $parentId === '' ? null : (int)$parentId;
        $queryParams = $this->request->getQueryParams();
        $filters = $queryParams['filters'] ?? [];
        $adapter = new TabulatorAdapter();
        $allowedIds = is_array($filters) && $filters !== []
            ? $this->filteredDepartmentIds($table, $adapter)
            : null;

        $sortQueryParams = $queryParams;
        unset($sortQueryParams['filters']);
        $sortRequest = $this->request->withQueryParams($sortQueryParams);
        $service = new DepartmentManagementService();
        if ($allowedIds !== null && $parentId === null) {
            $query = $table->find()
                ->where($allowedIds === [] ? ['1 = 0'] : ['Departments.id IN' => $allowedIds])
                ->orderBy(['Departments.lft' => 'ASC'])
                ->contain(['CgrStrategies', 'Managers']);
            $query = $adapter->adaptRequest($sortRequest, $query);
            /** @var list<\App\Model\Entity\Department> $departments */
            $departments = $query->all()->toList();
            $rows = $this->filteredDepartmentRows($departments, $service, $allowedIds);
        } else {
            $query = $table->find('activeChildren', parentId: $parentId)
                ->contain(['CgrStrategies', 'Managers']);
            if ($allowedIds !== null) {
                $query->where($allowedIds === [] ? ['1 = 0'] : ['Departments.id IN' => $allowedIds]);
            }
            $query = $adapter->adaptRequest($sortRequest, $query);
            /** @var list<\App\Model\Entity\Department> $departments */
            $departments = $query->all()->toList();
            $rows = array_map(
                fn(Department $department): array => $this->departmentRow($department, $service, $allowedIds),
                $departments,
            );
        }
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
            $saved = (new DepartmentManagementService())->save(
                $department,
                $this->authorizedRequestData('Departments'),
            );
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
            $saved = (new DepartmentManagementService())->save(
                $department,
                $this->authorizedRequestData('Departments'),
            );
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
        /** @var \App\Model\Entity\User $actor */
        $actor = $this->request->getAttribute('identity')->getOriginalData();
        $cleanup = $this->request->getData('cleanup', []);
        if (!is_array($cleanup)) {
            $cleanup = [];
        }
        $service = new DepartmentManagementService();
        $recipients = $service->notificationRecipients($department, $actor);
        $fullReportRecipientIds = $service->fullReportRecipientIds($department, $actor);
        try {
            $report = $service->deactivateWithCleanup(
                $department,
                array_values(array_map('strval', $cleanup)),
            );
        } catch (DomainException $exception) {
            return $this->domainError($exception);
        }

        $sent = 0;
        $failed = 0;
        foreach ($recipients as $recipient) {
            $recipientId = (int)$recipient->get('id');
            $fullReport = in_array($recipientId, $fullReportRecipientIds, true)
                || !in_array($recipientId, $report['detached_user_ids'], true);
            if ((new DepartmentDeactivationMailer())->safeSend('report', [$recipient, $report, $fullReport])) {
                $sent++;
            } else {
                $failed++;
            }
        }
        foreach ($this->auditRecipients() as $recipient) {
            if ((new DepartmentDeactivationMailer())->safeSend('auditReport', [$recipient, $report])) {
                $sent++;
            } else {
                $failed++;
            }
        }

        $message = $failed === 0
            ? __('Le département a été désactivé et le compte rendu a été envoyé.')
            : __('Le département a été désactivé, mais {0} courriel(s) n’ont pas été remis.', $failed);

        return $this->jsonSuccess([
            'report' => $report,
            'email' => ['sent' => $sent, 'failed' => $failed],
        ], $message);
    }

    /** Retourne les codes CGR actifs d’un département. */
    public function cgrCodes(string $departmentId): void
    {
        $department = $this->activeDepartment($departmentId);
        $this->Authorization->authorize($department, 'view');
        $table = $this->fetchTable('CgrCodes');
        $query = (new TabulatorAdapter())->adaptRequest(
            $this->request,
            $table->find()
                ->matching('Departments', static function ($query) use ($department): SelectQuery {
                    return $query->where(['Departments.id' => $department->get('id')]);
                })
                ->where(['CgrCodes.active' => true]),
        );
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
        $code = $table->newEmptyEntity();
        $this->Authorization->authorize($code, 'add');
        $code = $table->patchEntity($code, $this->authorizedRequestData('CgrCodes'), [
            'accessibleFields' => ['is_system' => false],
        ]);
        if (!$table->save($code)) {
            return $this->validationErrorResponse($code, 'CgrCodes');
        }
        /** @var \Cake\ORM\Association\BelongsToMany<\App\Model\Table\CgrCodesTable> $cgrAssociation */
        $cgrAssociation = $this->fetchTable('Departments')->getAssociation('CgrCodes');
        $cgrAssociation->link($department, [$code]);

        return $this->jsonSuccess(['id' => $code->get('id'), 'data' => $code], __('Le code CGR a été créé.'));
    }

    /** Modifie un code CGR. */
    public function editCgrCode(string $departmentId, string $id): Response
    {
        $this->activeDepartment($departmentId);
        $table = $this->fetchTable('CgrCodes');
        /** @var \App\Model\Entity\CgrCode $code */
        $code = $table->find()
            ->matching('Departments', static function ($query) use ($departmentId): SelectQuery {
                return $query->where(['Departments.id' => $departmentId]);
            })
            ->where(['CgrCodes.id' => $id, 'CgrCodes.active' => true])
            ->firstOrFail();
        $this->Authorization->authorize($code, 'edit');
        $code = $table->patchEntity($code, $this->authorizedRequestData('CgrCodes'), [
            'accessibleFields' => ['type' => false, 'is_system' => false],
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
        $code = $table->find()
            ->matching('Departments', static function ($query) use ($departmentId): SelectQuery {
                return $query->where(['Departments.id' => $departmentId]);
            })
            ->where(['CgrCodes.id' => $id, 'CgrCodes.active' => true])
            ->firstOrFail();
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
            ->contain(['CgrStrategies', 'Managers', 'CgrCodes'])
            ->where(['Departments.id' => $id, 'Departments.deleted IS' => null])
            ->firstOrFail();

        return $department;
    }

    /** @return list<string> */
    private function auditRecipients(): array
    {
        $configured = (string)env('DEPARTMENT_DEACTIVATION_AUDIT_RECIPIENTS', '');
        $recipients = preg_split('/[,;]+/', $configured) ?: [];

        return array_values(array_filter(
            array_map(static fn(string $recipient): string => trim($recipient), $recipients),
            static fn(string $recipient): bool => filter_var($recipient, FILTER_VALIDATE_EMAIL) !== false,
        ));
    }

    /**
     * @param list<int>|null $allowedIds Identifiants de la branche filtrée.
     * @param list<array<string, mixed>>|null $loadedChildren Enfants déjà construits.
     * @return array<string, mixed>
     */
    private function departmentRow(
        Department $department,
        DepartmentManagementService $service,
        ?array $allowedIds = null,
        ?array $loadedChildren = null,
    ): array {
        $hasChildren = $loadedChildren !== null
            ? $loadedChildren !== []
            : ($allowedIds === null
                ? $service->hasChildren($department)
                : $this->hasAllowedChildren($department, $allowedIds));

        return [
            'id' => (int)$department->get('id'),
            'parent_id' => $department->get('parent_id') === null ? null : (int)$department->get('parent_id'),
            'code' => $department->get('code'),
            'name' => $department->get('name'),
            'sort' => $department->get('sort'),
            'level' => (int)$department->get('level'),
            'department_type_id' => (int)$department->get('department_type_id'),
            'managers' => array_values(array_filter(array_map(
                static fn($manager): string => (string)$manager->get('display_name'),
                $department->get('managers') ?: [],
            ))),
            'cgr_strategy' => $department->get('cgr_strategy')?->get('name'),
            'cgr_codes' => array_values(array_map(
                static fn($code): array => [
                    'id' => (int)$code->get('id'),
                    'type' => (string)$code->get('type'),
                    'code' => (string)$code->get('code'),
                    'label' => (string)$code->get('label'),
                    'active' => (bool)$code->get('active'),
                ],
                $department->get('cgr_codes') ?: [],
            )),
            'has_children' => $hasChildren,
            'children' => $loadedChildren ?? ($hasChildren
                ? [[
                    'id' => 'loading-' . $department->get('id'),
                    '_tree_placeholder' => true,
                    'name' => __('Chargement…'),
                ]]
                : []),
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

    /**
     * Construit les branches complètes lorsque la grille est filtrée.
     *
     * @param list<\App\Model\Entity\Department> $departments Départements autorisés.
     * @param list<int> $allowedIds Identifiants de la branche filtrée.
     * @return list<array<string, mixed>>
     */
    private function filteredDepartmentRows(
        array $departments,
        DepartmentManagementService $service,
        array $allowedIds,
    ): array {
        $childrenByParent = [];
        foreach ($departments as $department) {
            $parentId = $department->get('parent_id');
            $key = $parentId === null ? 'root' : (string)$parentId;
            $childrenByParent[$key][] = $department;
        }

        $buildRows = function (?int $parentId) use (&$buildRows, $childrenByParent, $service, $allowedIds): array {
            $key = $parentId === null ? 'root' : (string)$parentId;
            $rows = [];
            foreach ($childrenByParent[$key] ?? [] as $department) {
                $children = $buildRows((int)$department->get('id'));
                $rows[] = $this->departmentRow($department, $service, $allowedIds, $children);
            }

            return $rows;
        };

        return $buildRows(null);
    }

    /**
     * Retourne les départements correspondant au filtre et leurs ancêtres.
     *
     * @return list<int> Identifiants autorisés dans la branche filtrée.
     */
    private function filteredDepartmentIds(DepartmentsTable $table, TabulatorAdapter $adapter): array
    {
        $matchingQuery = $table->find()->where(['Departments.deleted IS' => null]);
        $matchingQuery = $this->adaptDepartmentFilters($matchingQuery, $adapter, $this->request);
        /** @var list<\App\Model\Entity\Department> $matchingDepartments */
        $matchingDepartments = $matchingQuery
            ->select(['id', 'lft', 'rght'])
            ->all()
            ->toList();

        if ($matchingDepartments === []) {
            return [];
        }

        /** @var list<\App\Model\Entity\Department> $activeDepartments */
        $activeDepartments = $table->find()
            ->select(['id', 'lft', 'rght'])
            ->where(['Departments.deleted IS' => null])
            ->all()
            ->toList();
        $allowedIds = [];
        foreach ($matchingDepartments as $matchingDepartment) {
            $matchingLft = (int)$matchingDepartment->get('lft');
            $matchingRght = (int)$matchingDepartment->get('rght');
            foreach ($activeDepartments as $candidate) {
                if (
                    (int)$candidate->get('lft') <= $matchingLft
                    && (int)$candidate->get('rght') >= $matchingRght
                ) {
                    $id = (int)$candidate->get('id');
                    $allowedIds[$id] = $id;
                }
            }
        }

        return array_values($allowedIds);
    }

    /**
     * Adapte les filtres de la grille aux champs calculés de l'arbre.
     *
     * Le responsable et la stratégie CGR sont exposés par l'API comme des
     * valeurs d'affichage, mais leurs données sont stockées dans les
     * associations Users aliasée Managers et CgrStrategies.
     *
     * @param \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> $query Requête des départements.
     * @return \Cake\ORM\Query\SelectQuery<\App\Model\Entity\Department>
     */
    private function adaptDepartmentFilters(
        SelectQuery $query,
        TabulatorAdapter $adapter,
        ServerRequest $request,
    ): SelectQuery {
        $queryParams = $request->getQueryParams();
        $filters = $queryParams['filters'] ?? [];
        if (!is_array($filters) || $filters === []) {
            /** @var \Cake\ORM\Query\SelectQuery<\App\Model\Entity\Department> $adaptedQuery */
            $adaptedQuery = $adapter->adaptRequest($request, $query);

            return $adaptedQuery;
        }

        $standardFilters = [];
        $managerFilters = [];
        $strategyFilters = [];
        foreach ($filters as $filter) {
            if (!is_array($filter)) {
                continue;
            }
            $field = $filter['field'] ?? null;
            if ($field === 'manager' || $field === 'managers') {
                $managerFilters[] = $filter;
                continue;
            }
            if ($field === 'cgr_strategy' || $field === 'cgr_strategies') {
                $strategyFilters[] = $filter;
                continue;
            }
            $standardFilters[] = $filter;
        }

        if ($managerFilters !== []) {
            $query->leftJoinWith('Managers');
            foreach ($managerFilters as $filter) {
                $value = $filter['value'] ?? '';
                if (!is_string($value) || $value === '') {
                    continue;
                }
                $type = strtolower((string)($filter['type'] ?? 'like'));
                $operator = in_array($type, ['=', '!=', '<', '<=', '>', '>='], true)
                    ? $type
                    : 'LIKE';
                $value = $operator === 'LIKE' ? '%' . $value . '%' : $value;
                $query->where(function (QueryExpression $expression) use ($operator, $value) {
                    return $expression->or([
                        'Managers.firstname ' . $operator => $value,
                        'Managers.lastname ' . $operator => $value,
                    ]);
                });
            }
        }

        if ($strategyFilters !== []) {
            $query->leftJoinWith('CgrStrategies');
            foreach ($strategyFilters as $filter) {
                $value = $filter['value'] ?? '';
                if (!is_string($value) || $value === '') {
                    continue;
                }
                $type = strtolower((string)($filter['type'] ?? 'like'));
                $operator = in_array($type, ['=', '!=', '<', '<=', '>', '>='], true)
                    ? $type
                    : 'LIKE';
                $value = $operator === 'LIKE' ? '%' . $value . '%' : $value;
                $query->where(['CgrStrategies.name ' . $operator => $value]);
            }
        }

        $queryParams['filters'] = $standardFilters;
        /** @var \Cake\ORM\Query\SelectQuery<\App\Model\Entity\Department> $adaptedQuery */
        $adaptedQuery = $adapter->adaptRequest($request->withQueryParams($queryParams), $query);

        return $adaptedQuery;
    }

    /**
     * Vérifie qu'un nœud possède un enfant dans la branche filtrée.
     *
     * @param list<int> $allowedIds Identifiants de la branche filtrée.
     */
    private function hasAllowedChildren(Department $department, array $allowedIds): bool
    {
        if ($allowedIds === []) {
            return false;
        }

        return $this->fetchTable('Departments')->find()
            ->where([
                'Departments.parent_id' => $department->get('id'),
                'Departments.deleted IS' => null,
                'Departments.id IN' => $allowedIds,
            ])
            ->count() > 0;
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
