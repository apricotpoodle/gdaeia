<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use App\Model\Entity\User;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use RuntimeException;

/** API de configuration des rôles validateurs par sous-arbre de départements. */
class ValidationsequencesController extends AppController
{
    /** @return void */
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setClassName('Json');
    }

    /** Retourne l'arbre des départements sélectionnables. */
    public function departmentsTree(): void
    {
        $this->request->allowMethod(['get']);
        $this->authorizeManagement();
        $departments = $this->fetchTable('Departments')->find('treeThreadedVisibleTo', user: $this->getOperator())->all();
        $this->set('data', $departments);
        $this->viewBuilder()->setOption('serialize', ['data']);
    }

    /** Retourne les rôles validateurs administrables. */
    public function roles(): void
    {
        $this->request->allowMethod(['get']);
        $this->authorizeManagement();
        $roles = $this->fetchTable('Roles')->find('roleAccessVisibleTo', user: $this->getOperator())
            ->orderByAsc('Roles.name')->all();
        $this->set('data', $roles);
        $this->viewBuilder()->setOption('serialize', ['data']);
    }

    /** Retourne les rôles présents sur tous les départements sélectionnés. */
    public function assignedRoles(): void
    {
        $this->request->allowMethod(['get']);
        $this->authorizeManagement();
        $rawDepartmentIds = $this->request->getQuery('department_ids');
        if (!is_array($rawDepartmentIds) || $rawDepartmentIds === []) {
            $this->set('data', []);
            $this->viewBuilder()->setOption('serialize', ['data']);

            return;
        }
        $departmentIds = $this->resolveDepartmentIds($rawDepartmentIds);

        $sequences = $this->Validationsequences->find()
            ->select([
                'role_id',
                'sequence' => 'MIN(Validationsequences.sequence)',
                'sequence_max' => 'MAX(Validationsequences.sequence)',
            ])
            ->innerJoinWith('Roles')
            ->contain(['Roles'])
            ->where(['Validationsequences.department_id IN' => $departmentIds, 'Validationsequences.deleted IS' => null])
            ->groupBy(['Validationsequences.role_id'])
            ->having(['COUNT(DISTINCT Validationsequences.department_id) =' => count($departmentIds)])
            ->orderByAsc('Roles.name')
            ->all()
            ->toList();

        $data = array_map(static function ($sequence): array {
            return [
                'id' => (int)$sequence->role_id,
                'code' => (string)$sequence->role->code,
                'name' => (string)$sequence->role->name,
                'sequence' => (int)$sequence->sequence === (int)$sequence->sequence_max ? (int)$sequence->sequence : null,
            ];
        }, $sequences);
        $this->set('data', $data);
        $this->viewBuilder()->setOption('serialize', ['data']);
    }

    /** Affecte un rôle à la racine et à tous les descendants sélectionnés. */
    public function assignRole(): Response
    {
        $this->request->allowMethod(['post']);
        $this->authorizeManagement();
        $departmentIds = $this->resolveDepartmentIds($this->request->getData('department_ids'));
        $roleId = $this->positiveInteger($this->request->getData('role_id'), 'role_id');
        $sequence = $this->positiveInteger($this->request->getData('sequence') ?? 1, 'sequence');
        $operator = $this->getOperator();
        $role = $this->fetchTable('Roles')->find('roleAccessVisibleTo', user: $operator)->where(['Roles.id' => $roleId])->first();
        if ($role === null) {
            throw new NotFoundException(__('Le rôle validateur est introuvable ou inactif.'));
        }

        try {
            $created = $this->Validationsequences->getConnection()->transactional(function () use ($departmentIds, $roleId, $sequence): int {
                $existing = $this->Validationsequences->find()
                ->where(['department_id IN' => $departmentIds, 'role_id' => $roleId])
                ->all()->indexBy('department_id')->toArray();
                $created = 0;
                foreach ($departmentIds as $departmentId) {
                    if (isset($existing[$departmentId])) {
                        if ($existing[$departmentId]->deleted !== null) {
                            $existing[$departmentId]->patch([
                            'deleted' => null,
                            'sequence' => $sequence,
                            'name' => '',
                            ]);
                            if (!$this->Validationsequences->save($existing[$departmentId])) {
                                throw new RuntimeException(__('Impossible de réactiver la séquence de validation.'));
                            }
                            $created++;
                        }
                        continue;
                    }
                    $entity = $this->Validationsequences->newEntity([
                    'department_id' => $departmentId,
                    'role_id' => $roleId,
                    'sequence' => $sequence,
                    'name' => '',
                    ]);
                    if (!$this->Validationsequences->save($entity)) {
                        throw new RuntimeException(__('Impossible d’enregistrer la séquence de validation.'));
                    }
                    $created++;
                }
                $this->assertContiguousSequences($departmentIds);

                return $created;
            });
        } catch (BadRequestException | RuntimeException $exception) {
            return $this->jsonError($exception->getMessage());
        }

        return $this->jsonSuccess(['associations_created' => $created]);
    }

    /** Retire un rôle de tous les départements sélectionnés. */
    public function unassignRole(): Response
    {
        $this->request->allowMethod(['post']);
        $this->authorizeManagement();
        $departmentIds = $this->resolveDepartmentIds($this->request->getData('department_ids'));
        $roleId = $this->positiveInteger($this->request->getData('role_id'), 'role_id');
        try {
            $deleted = $this->Validationsequences->getConnection()->transactional(function () use ($departmentIds, $roleId): int {
                $deleted = $this->Validationsequences->deleteAll([
                'department_id IN' => $departmentIds,
                'role_id' => $roleId,
                'deleted IS' => null,
                ]);
                $this->assertContiguousSequences($departmentIds);

                return $deleted;
            });
        } catch (BadRequestException $exception) {
            return $this->jsonError($exception->getMessage());
        }

        return $this->jsonSuccess(['associations_deleted' => $deleted]);
    }

    /** Définit manuellement le numéro de séquence pour la sélection. */
    public function updateSequence(): Response
    {
        $this->request->allowMethod(['post']);
        $this->authorizeManagement();
        $departmentIds = $this->resolveDepartmentIds($this->request->getData('department_ids'));
        $roleId = $this->positiveInteger($this->request->getData('role_id'), 'role_id');
        $sequence = $this->positiveInteger($this->request->getData('sequence'), 'sequence');
        try {
            $updated = $this->Validationsequences->getConnection()->transactional(function () use ($departmentIds, $roleId, $sequence): int {
                $updated = $this->Validationsequences->updateAll(['sequence' => $sequence], [
                'department_id IN' => $departmentIds,
                'role_id' => $roleId,
                'deleted IS' => null,
                ]);
                $this->assertContiguousSequences($departmentIds);

                return $updated;
            });
        } catch (BadRequestException $exception) {
            return $this->jsonError($exception->getMessage());
        }

        return $this->jsonSuccess(['associations_updated' => $updated]);
    }

    /** @return void */
    private function authorizeManagement(): void
    {
        $this->Authorization->authorize($this->Validationsequences->newEmptyEntity(), 'manage');
    }

    /** @param array<int> $departmentIds @return void */
    private function assertContiguousSequences(array $departmentIds): void
    {
        if (!$this->Validationsequences->hasContiguousSequencesForDepartments($departmentIds)) {
            throw new BadRequestException(__('Chaque département doit disposer d’une séquence continue, commençant à 1.'));
        }
    }

    /** @param mixed $rawDepartmentIds @return list<int> */
    private function resolveDepartmentIds(mixed $rawDepartmentIds): array
    {
        $selectedIds = $this->positiveIntegerList($rawDepartmentIds, 'department_ids');
        $visibleDepartments = $this->fetchTable('Departments')->find('visibleTo', user: $this->getOperator())
            ->select(['id', 'lft', 'rght'])->all()->toList();
        $selectedDepartments = array_filter($visibleDepartments, static fn($department): bool => in_array((int)$department->id, $selectedIds, true));
        if (count($selectedDepartments) !== count($selectedIds)) {
            throw new ForbiddenException(__('Au moins un département ciblé est hors de votre périmètre.'));
        }

        $departmentIds = [];
        foreach ($visibleDepartments as $department) {
            foreach ($selectedDepartments as $selectedDepartment) {
                if ($department->lft >= $selectedDepartment->lft && $department->rght <= $selectedDepartment->rght) {
                    $departmentIds[] = (int)$department->id;
                    break;
                }
            }
        }

        return array_values(array_unique($departmentIds));
    }

    /** @param mixed $values @param string $field @return list<int> */
    private function positiveIntegerList(mixed $values, string $field): array
    {
        if (!is_array($values) || $values === []) {
            throw new BadRequestException(__('Le champ « {0} » doit être une liste non vide d’identifiants.', $field));
        }

        return array_values(array_unique(array_map(fn($value): int => $this->positiveInteger($value, $field), $values)));
    }

    /** @param mixed $value @param string $field @return int */
    private function positiveInteger(mixed $value, string $field): int
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false || (int)$value < 1) {
            throw new BadRequestException(__('Le champ « {0} » doit être un entier positif.', $field));
        }

        return (int)$value;
    }

    /** @return \App\Model\Entity\User */
    private function getOperator(): User
    {
        $user = $this->request->getAttribute('identity')?->getOriginalData();
        if (!$user instanceof User) {
            throw new ForbiddenException(__('Opérateur invalide.'));
        }

        return $user;
    }

    /** @param array<string, int> $data @return \Cake\Http\Response */
    private function jsonSuccess(array $data): Response
    {
        return $this->response->withType('application/json')->withStringBody(json_encode(['success' => true] + $data));
    }

    /** @return \Cake\Http\Response */
    private function jsonError(string $message): Response
    {
        return $this->response->withType('application/json')->withStatus(400)
            ->withStringBody(json_encode(['success' => false, 'message' => $message], JSON_UNESCAPED_UNICODE));
    }
}
