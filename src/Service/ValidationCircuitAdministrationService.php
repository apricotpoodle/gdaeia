<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\User;
use App\Model\Entity\ValidationCircuitRule;
use App\Policy\ValidationCircuitRulePolicy;
use App\Service\Workflow\ValidationCircuitResolver;
use Cake\Http\Exception\ForbiddenException;
use Cake\ORM\TableRegistry;
use DomainException;

/** Administre les règles et habilitations locales dans des transactions atomiques. */
final class ValidationCircuitAdministrationService
{
    /**
     * Contrôle les identifiants sans expansion implicite de l'arbre.
     *
     * @return list<int>
     */
    public function departmentIds(mixed $values): array
    {
        if (!is_array($values) || $values === []) {
            throw new DomainException(__('Sélectionnez au moins un département.'));
        }
        $ids = [];
        foreach ($values as $value) {
            $ids[] = $this->positiveId($value);
        }

        return array_values(array_unique($ids));
    }

    /** Valide un identifiant issu d'une requête sans troncature silencieuse. */
    public function positiveId(mixed $value): int
    {
        if ((!is_int($value) && !is_string($value)) || !preg_match('/^[1-9][0-9]*$/D', (string)$value)) {
            throw new DomainException(__('Les identifiants sélectionnés doivent être des entiers positifs.'));
        }
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            throw new DomainException(__('Un identifiant sélectionné est invalide.'));
        }

        return $id;
    }

    /**
     * Prévisualise chaque département après contrôle intégral du périmètre.
     *
     * @param list<int> $departmentIds
     * @return list<array<string, mixed>>
     */
    public function preview(User $operator, array $departmentIds, int $contracttypeId, int $finalRoleId): array
    {
        $this->assertDepartmentsAllowed($operator, $departmentIds);
        $this->assertActiveReference('Contracttypes', $contracttypeId);
        $resolver = new ValidationCircuitResolver();
        $departments = TableRegistry::getTableLocator()->get('Departments');
        $previews = [];
        foreach ($departmentIds as $departmentId) {
            $result = $resolver->resolve($departmentId, $contracttypeId, $finalRoleId);
            $previews[] = [
                'department_id' => $departmentId,
                'department_name' => (string)$departments->get($departmentId)->get('name'),
                'sequences' => array_map(static fn($sequence): array => [
                    'role_id' => (int)$sequence->get('role_id'),
                    'role_name' => (string)$sequence->get('role')?->get('name'),
                    'sequence' => (int)$sequence->get('sequence'),
                ], $result['sequences']),
                'issues' => $result['issues'],
            ];
        }

        return $previews;
    }

    /**
     * Crée toutes les règles ou annule entièrement le lot.
     *
     * @param list<int> $departmentIds
     * @return list<int>
     */
    public function create(User $operator, array $departmentIds, int $contracttypeId, int $finalRoleId): array
    {
        $table = TableRegistry::getTableLocator()->get('ValidationCircuitRules');

        return $table->getConnection()->transactional(function () use (
            $table,
            $operator,
            $departmentIds,
            $contracttypeId,
            $finalRoleId,
        ): array {
            $this->assertValidPreviews($this->preview($operator, $departmentIds, $contracttypeId, $finalRoleId));
            $conflicts = $table->find()->contain(['Departments'])
                ->where([
                    'ValidationCircuitRules.department_id IN' => $departmentIds,
                    'contracttype_id' => $contracttypeId,
                ])
                ->all();
            $names = [];
            foreach ($conflicts as $conflict) {
                $names[] = (string)$conflict->get('department')->get('name');
            }
            if ($names !== []) {
                throw new DomainException(__(
                    'Une règle existe déjà pour les départements suivants : {0}.',
                    implode(', ', $names),
                ));
            }
            $ids = [];
            foreach ($departmentIds as $departmentId) {
                $rule = $table->newEntity([
                    'department_id' => $departmentId,
                    'contracttype_id' => $contracttypeId,
                    'final_role_id' => $finalRoleId,
                ]);
                $table->saveOrFail($rule);
                $ids[] = (int)$rule->get('id');
            }

            return $ids;
        });
    }

    /**
     * Modifie une règle après contrôle de son ancien et de son nouveau département.
     *
     * @param array<string, mixed> $data Champs déjà filtrés par l'ACL HTTP.
     */
    public function update(User $operator, int $id, array $data): void
    {
        $table = TableRegistry::getTableLocator()->get('ValidationCircuitRules');
        $table->getConnection()->transactional(function () use ($table, $operator, $id, $data): void {
            /** @var \App\Model\Entity\ValidationCircuitRule $rule */
            $rule = $table->get($id);
            $this->assertDepartmentsAllowed($operator, [(int)$rule->get('department_id')]);
            $values = [];
            foreach (['department_id', 'contracttype_id', 'final_role_id'] as $field) {
                $values[$field] = array_key_exists($field, $data)
                    ? $this->positiveId($data[$field]) : (int)$rule->get($field);
            }
            $this->assertValidPreviews($this->preview(
                $operator,
                [$values['department_id']],
                $values['contracttype_id'],
                $values['final_role_id'],
            ));
            $table->saveOrFail($table->patchEntity($rule, $values));
        });
    }

    /** Retire une règle sans toucher aux étapes des cycles déjà lancés. */
    public function remove(User $operator, int $id): void
    {
        $table = TableRegistry::getTableLocator()->get('ValidationCircuitRules');
        $table->getConnection()->transactional(function () use ($table, $operator, $id): void {
            $rule = $table->get($id);
            $this->assertDepartmentsAllowed($operator, [(int)$rule->get('department_id')]);
            $table->deleteOrFail($rule);
        });
    }

    /**
     * Désigne un rôle gestionnaire ; les doublons existants sont conservés.
     *
     * @param list<int> $departmentIds
     */
    public function assignManager(User $operator, array $departmentIds, int $roleId): void
    {
        $this->assertGrantManagement($operator);
        $table = TableRegistry::getTableLocator()->get('ValidationCircuitManagers');
        $table->getConnection()->transactional(function () use ($table, $operator, $departmentIds, $roleId): void {
            $this->assertGrantManagement($operator);
            $this->assertDepartmentsAllowed($operator, $departmentIds);
            $this->assertActiveReference('Roles', $roleId);
            foreach ($departmentIds as $departmentId) {
                if ($table->find()->where(['department_id' => $departmentId, 'role_id' => $roleId])->count() > 0) {
                    continue;
                }
                $table->saveOrFail($table->newEntity(['department_id' => $departmentId, 'role_id' => $roleId]));
            }
        });
    }

    /** Révoque une habilitation même lorsque sa référence a été désactivée. */
    public function revokeManager(User $operator, int $id): void
    {
        $this->assertGrantManagement($operator);
        $table = TableRegistry::getTableLocator()->get('ValidationCircuitManagers');
        $table->deleteOrFail($table->get($id));
    }

    /** @param list<int> $departmentIds */
    private function assertDepartmentsAllowed(User $operator, array $departmentIds): void
    {
        if ($departmentIds === []) {
            throw new DomainException(__('Sélectionnez au moins un département.'));
        }
        $policy = new ValidationCircuitRulePolicy();
        foreach ($departmentIds as $departmentId) {
            if (!$policy->canManage($operator, new ValidationCircuitRule(['department_id' => $departmentId]))) {
                throw new ForbiddenException(__(
                    'Au moins un département ciblé est hors de votre périmètre de gestion des circuits.',
                ));
            }
            $this->assertActiveReference('Departments', $departmentId);
        }
    }

    /** Contrôle les référentiels côté serveur, indépendamment des sélecteurs. */
    private function assertActiveReference(string $alias, int $id): void
    {
        $count = TableRegistry::getTableLocator()->get($alias)->find()
            ->where(['id' => $id, 'deleted IS' => null])->count();
        if ($count !== 1) {
            throw new DomainException(__('La référence sélectionnée est introuvable ou inactive.'));
        }
    }

    /** @param list<array<string, mixed>> $previews */
    private function assertValidPreviews(array $previews): void
    {
        $issues = [];
        foreach ($previews as $preview) {
            foreach ($preview['issues'] as $issue) {
                $issues[] = $issue;
            }
        }
        if ($issues !== []) {
            throw new DomainException(implode(' ', array_unique($issues)));
        }
    }

    /** Contrôle également les mutations appelées directement sur le service. */
    private function assertGrantManagement(User $operator): void
    {
        if (!(new ValidationCircuitRulePolicy())->canManageGrants($operator, new ValidationCircuitRule())) {
            throw new ForbiddenException(__(
                'Seuls les super-administrateurs désignent les gestionnaires de circuits.',
            ));
        }
    }
}
