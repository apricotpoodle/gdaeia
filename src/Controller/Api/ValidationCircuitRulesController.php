<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use App\Model\Entity\User;
use App\Service\DataGrid\TabulatorAdapter;
use App\Service\ValidationCircuitAdministrationService;
use App\Service\Workflow\ValidationCircuitResolver;
use Cake\Http\Response;
use Cake\ORM\Exception\PersistenceFailedException;
use Cake\ORM\Query\SelectQuery;
use DomainException;

/** API des règles locales et des désignations de leurs gestionnaires. */
final class ValidationCircuitRulesController extends AppController
{
    /** Configure le rendu JSON. */
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setClassName('Json');
    }

    /** Retourne les règles du périmètre avec pagination, tri et filtres distants. */
    public function index(): void
    {
        $this->request->allowMethod(['get']);
        $this->authorizeRules();
        $query = $this->fetchTable('ValidationCircuitRules')->find('visibleTo', user: $this->operator())
            ->contain(['Departments', 'Contracttypes', 'FinalRoles']);
        $this->grid($query, [
            'id' => 'ValidationCircuitRules.id',
            'department_id' => 'ValidationCircuitRules.department_id',
            'contracttype_id' => 'ValidationCircuitRules.contracttype_id',
            'final_role_id' => 'ValidationCircuitRules.final_role_id',
            'department.name' => 'Departments.name',
            'contracttype.name' => 'Contracttypes.name',
            'final_role.name' => 'FinalRoles.name',
        ], true);
    }

    /** Retourne seulement les départements sur lesquels l'opérateur peut agir. */
    public function departmentsTree(): void
    {
        $this->request->allowMethod(['get']);
        $this->authorizeRules();
        $data = $this->fetchTable('Departments')->find('circuitManageableTo', user: $this->operator())
            ->select(['id', 'parent_id', 'name', 'lft'])->orderByAsc('Departments.lft')
            ->find('threaded')->all()->toList();
        $this->serializeData($data);
    }

    /** Fournit les référentiels actifs sans ouvrir leur administration. */
    public function options(): void
    {
        $this->request->allowMethod(['get']);
        $this->authorizeRules();
        $data = [];
        foreach (['contracttypes' => 'Contracttypes', 'roles' => 'Roles'] as $key => $alias) {
            $data[$key] = $this->fetchTable($alias)->find()->select(['id', 'name'])
                ->where(['deleted IS' => null])->orderByAsc('name')->all()->toList();
        }
        $this->serializeData($data);
    }

    /** Prévisualise les étapes retenues et les incohérences par département. */
    public function preview(): Response
    {
        $this->request->allowMethod(['post']);
        $this->authorizeRules();
        $service = new ValidationCircuitAdministrationService();
        try {
            $data = $this->request->getData();
            $preview = $service->preview(
                $this->operator(),
                $service->departmentIds($data['department_ids'] ?? null),
                $service->positiveId($data['contracttype_id'] ?? null),
                $service->positiveId($data['final_role_id'] ?? null),
            );

            return $this->jsonResult(
                true,
                __('L’éligibilité du demandeur sera vérifiée au lancement de la DAE.'),
                $preview,
            );
        } catch (DomainException $exception) {
            return $this->jsonResult(false, $exception->getMessage());
        }
    }

    /** Crée un lot explicite de règles, sans remplacement en cas de doublon. */
    public function add(): Response
    {
        $this->request->allowMethod(['post']);
        $this->authorizeRules('add');
        $service = new ValidationCircuitAdministrationService();
        try {
            $raw = $this->request->getData();
            $data = $this->authorizedRequestData('ValidationCircuitRules', [
                'contracttype_id' => $raw['contracttype_id'] ?? null,
                'final_role_id' => $raw['final_role_id'] ?? null,
            ]);
            $rawIds = $raw['department_ids'] ?? (isset($raw['department_id']) ? [$raw['department_id']] : null);
            $departmentIds = $service->departmentIds($rawIds);
            foreach ($departmentIds as $departmentId) {
                $filtered = $this->authorizedRequestData('ValidationCircuitRules', ['department_id' => $departmentId]);
                $service->positiveId($filtered['department_id'] ?? null);
            }
            $ids = $service->create(
                $this->operator(),
                $departmentIds,
                $service->positiveId($data['contracttype_id'] ?? null),
                $service->positiveId($data['final_role_id'] ?? null),
            );

            return $this->jsonResult(
                true,
                __('Les règles de circuit ont été créées.'),
                ['ids' => $ids, 'id' => $ids[0]],
            );
        } catch (DomainException $exception) {
            return $this->jsonResult(false, $exception->getMessage());
        } catch (PersistenceFailedException $exception) {
            return $this->validationErrorResponse($exception->getEntity(), 'ValidationCircuitRules');
        }
    }

    /** Modifie une règle après autorisation des périmètres avant et après mutation. */
    public function edit(string $id): Response
    {
        $this->request->allowMethod(['post', 'put', 'patch']);
        $rule = $this->fetchTable('ValidationCircuitRules')->get($id);
        $this->Authorization->authorize($rule, 'edit');
        try {
            (new ValidationCircuitAdministrationService())->update(
                $this->operator(),
                (int)$rule->get('id'),
                $this->authorizedRequestData('ValidationCircuitRules'),
            );

            return $this->jsonResult(true, __('La règle de circuit a été mise à jour.'));
        } catch (DomainException $exception) {
            return $this->jsonResult(false, $exception->getMessage());
        } catch (PersistenceFailedException $exception) {
            return $this->validationErrorResponse($exception->getEntity(), 'ValidationCircuitRules');
        }
    }

    /** Retire la règle pour les futurs lancements uniquement. */
    public function delete(string $id): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $rule = $this->fetchTable('ValidationCircuitRules')->get($id);
        $this->Authorization->authorize($rule, 'delete');
        (new ValidationCircuitAdministrationService())->remove($this->operator(), (int)$rule->get('id'));

        return $this->jsonResult(true, __('La règle de circuit a été retirée.'));
    }

    /** Retourne la grille distante des désignations, y compris devenues inactives. */
    public function managers(): void
    {
        $this->request->allowMethod(['get']);
        $this->authorizeRules('manageGrants');
        $query = $this->fetchTable('ValidationCircuitManagers')->find()->contain(['Departments', 'Roles']);
        $this->grid($query, [
            'id' => 'ValidationCircuitManagers.id',
            'department.name' => 'Departments.name',
            'role.name' => 'Roles.name',
        ]);
    }

    /** Désigne un rôle gestionnaire sur les départements sélectionnés. */
    public function assignManager(): Response
    {
        $this->request->allowMethod(['post']);
        $this->authorizeRules('manageGrants');
        $service = new ValidationCircuitAdministrationService();
        try {
            $data = $this->authorizedRequestData('ValidationCircuitManagers');
            $service->assignManager(
                $this->operator(),
                $service->departmentIds($data['department_ids'] ?? null),
                $service->positiveId($data['role_id'] ?? null),
            );

            return $this->jsonResult(true, __('Les habilitations de gestion ont été enregistrées.'));
        } catch (DomainException $exception) {
            return $this->jsonResult(false, $exception->getMessage());
        } catch (PersistenceFailedException $exception) {
            return $this->validationErrorResponse($exception->getEntity(), 'ValidationCircuitManagers');
        }
    }

    /** Révoque une désignation sans supprimer ses règles. */
    public function revokeManager(string $id): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $this->authorizeRules('manageGrants');
        (new ValidationCircuitAdministrationService())->revokeManager($this->operator(), (int)$id);

        return $this->jsonResult(true, __('L’habilitation de gestion a été retirée.'));
    }

    /** Autorise le point d'entrée par la Policy du domaine. */
    private function authorizeRules(string $action = 'index'): void
    {
        $this->Authorization->authorize($this->fetchTable('ValidationCircuitRules')->newEmptyEntity(), $action);
    }

    /** Retourne uniquement l'identité effective authentifiée. */
    private function operator(): User
    {
        /** @var \App\Model\Entity\User $operator */
        $operator = $this->request->getAttribute('identity')->getOriginalData();

        return $operator;
    }

    /**
     * Adapte une grille après liste blanche des champs de tri et filtrage.
     *
     * @param \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> $query
     * @param array<string, string> $map
     */
    private function grid(SelectQuery $query, array $map, bool $withCircuitIssues = false): void
    {
        $params = $this->request->getQueryParams();
        foreach (['sorters', 'filters'] as $key) {
            $params[$key] = array_values(array_filter(
                is_array($params[$key] ?? null) ? $params[$key] : [],
                static fn($item): bool => is_array($item) && isset($map[$item['field'] ?? '']),
            ));
        }
        $adapter = new TabulatorAdapter();
        $query = $adapter->adaptRequest($this->request->withQueryParams($params), $query, $map);
        $query->orderByAsc($query->getRepository()->getAlias() . '.id');
        $rows = $this->paginate($query, [
            'limit' => min(100, max(1, (int)$this->request->getQuery('size', 20))),
            'page' => max(1, (int)$this->request->getQuery('page', 1)),
            'sortableFields' => array_values($map),
        ]);
        $output = $adapter->adaptResponse($rows, $this->createGridRightsFormatter());
        if ($withCircuitIssues) {
            $resolver = new ValidationCircuitResolver();
            foreach ($output['data'] as $rule) {
                $resolved = $resolver->resolve(
                    (int)$rule->get('department_id'),
                    (int)$rule->get('contracttype_id'),
                    (int)$rule->get('final_role_id'),
                );
                $rule->set('configuration_issues', $resolved['issues']);
            }
        }
        $this->set($output);
        $this->viewBuilder()->setOption('serialize', array_keys($output));
    }

    /** @param array<mixed> $data */
    private function serializeData(array $data): void
    {
        $this->set('data', $data);
        $this->viewBuilder()->setOption('serialize', ['data']);
    }

    /** @param array<mixed> $data */
    private function jsonResult(bool $success, string $message, array $data = []): Response
    {
        return $this->response->withType('application/json')->withStatus($success ? 200 : 422)
            ->withStringBody((string)json_encode([
                'success' => $success, 'message' => $message,
                'errors' => $success ? [] : [$message], 'data' => $data,
            ], JSON_UNESCAPED_UNICODE));
    }
}
