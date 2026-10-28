<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use App\Model\Entity\User;
use App\Model\Entity\ValidationCircuitRule;
use App\Service\DataGrid\TabulatorAdapter;
use Cake\Http\Response;

/** API de gestion des fins de circuit locales. */
final class ValidationCircuitRulesController extends AppController
{
    /** Configure le rendu JSON. */
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setClassName('Json');
    }

    /** Retourne les règles visibles dans le périmètre de l'opérateur. */
    public function index(): void
    {
        $this->request->allowMethod(['get']);
        $table = $this->fetchTable('ValidationCircuitRules');
        $this->Authorization->authorize($table->newEmptyEntity(), 'index');
        /** @var \App\Model\Entity\User $operator */
        $operator = $this->request->getAttribute('identity')->getOriginalData();
        $query = $table->find()->contain(['Departments', 'Contracttypes', 'FinalRoles']);
        if (!$operator->isSuperUser() && !$operator->hasRole(User::ROLE_ADMIN)) {
            $departmentIds = $this->fetchTable('UserDepartments')->find('departmentsOf', user: $operator)
                ->all()->extract('department_id')->map(fn($id): int => (int)$id)->toList();
            $query->where(['department_id IN' => $departmentIds]);
        }
        $query = (new TabulatorAdapter())->adaptRequest($this->request, $query);
        $data = $this->paginate($query, [
            'limit' => max(1, (int)($this->request->getQuery('size') ?? 20)),
            'page' => max(1, (int)($this->request->getQuery('page') ?? 1)),
            'sortableFields' => ['department_id', 'contracttype_id', 'final_role_id'],
        ]);
        $output = (new TabulatorAdapter())->adaptResponse($data, $this->createGridRightsFormatter());
        $this->set($output);
        $this->viewBuilder()->setOption('serialize', array_keys($output));
    }

    /** Crée une règle locale. */
    public function add(): Response
    {
        $this->request->allowMethod(['post']);
        $table = $this->fetchTable('ValidationCircuitRules');
        $rule = $table->newEmptyEntity();
        $this->Authorization->authorize($rule, 'manage');
        $rule = $table->patchEntity($rule, $this->authorizedRequestData('ValidationCircuitRules'));
        $this->Authorization->authorize($rule, 'manage');
        if ($table->save($rule)) {
            return $this->jsonSuccess(
                ['id' => (int)$rule->get(ValidationCircuitRule::FIELD_ID)],
                __('La règle de circuit a été créée.'),
            );
        }

        return $this->validationErrorResponse($rule, 'ValidationCircuitRules');
    }

    /** Modifie une règle locale. */
    public function edit(string $id): Response
    {
        $this->request->allowMethod(['post', 'put', 'patch']);
        $table = $this->fetchTable('ValidationCircuitRules');
        $rule = $table->get((int)$id);
        $this->Authorization->authorize($rule, 'manage');
        $rule = $table->patchEntity($rule, $this->authorizedRequestData('ValidationCircuitRules'));
        $this->Authorization->authorize($rule, 'manage');
        if ($table->save($rule)) {
            return $this->jsonSuccess(message: __('La règle de circuit a été mise à jour.'));
        }

        return $this->validationErrorResponse($rule, 'ValidationCircuitRules');
    }

    /** Retire une règle et restaure le circuit complet par défaut. */
    public function delete(string $id): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $table = $this->fetchTable('ValidationCircuitRules');
        $rule = $table->get((int)$id);
        $this->Authorization->authorize($rule, 'manage');
        if (!$table->delete($rule)) {
            return $this->jsonError(__('Impossible de retirer la règle de circuit.'));
        }

        return $this->jsonSuccess(message: __('La règle de circuit a été retirée.'));
    }

    /** @param array<string, mixed> $data @return \Cake\Http\Response */
    private function jsonSuccess(array $data = [], ?string $message = null): Response
    {
        return $this->response->withType('application/json')->withStringBody((string)json_encode([
            'success' => true,
            'message' => $message ?? __('Opération réalisée avec succès.'),
            'data' => $data,
            'errors' => [],
        ], JSON_UNESCAPED_UNICODE));
    }

    /** @return \Cake\Http\Response */
    private function jsonError(string $message): Response
    {
        return $this->response->withType('application/json')->withStatus(422)
            ->withStringBody((string)json_encode([
                'success' => false,
                'message' => $message,
                'errors' => [$message],
            ], JSON_UNESCAPED_UNICODE));
    }
}
