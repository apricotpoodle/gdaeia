<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\Department;
use App\Model\Table\DepartmentsTable;
use Cake\I18n\DateTime;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use DomainException;

/** Centralise les règles d’administration de l’arbre des départements. */
final class DepartmentManagementService
{
    /** @var list<string> */
    private const EDITABLE_FIELDS = [
        'parent_id', 'cgr_code_id', 'code', 'name', 'sort', 'department_type_id',
        'cgr_strategy_id', 'current_manager_id',
    ];

    /** @param \App\Model\Table\DepartmentsTable|null $departments Table injectable for tests. */
    public function __construct(private readonly ?DepartmentsTable $departments = null)
    {
    }

    /** @return list<\App\Model\Entity\Department> */
    public function getChildren(?int $parentId): array
    {
        $query = $this->table()->find('activeChildren', parentId: $parentId)
            ->contain(['CgrStrategies', 'Managers']);

        /** @var list<\App\Model\Entity\Department> $departments */
        $departments = $query->all()->toList();

        return $departments;
    }

    /** Indique si un département possède au moins un enfant actif. */
    public function hasChildren(Department $department): bool
    {
        return $this->countActiveChildren($department) > 0;
    }

    /**
     * Retourne les dépendances qui peuvent bloquer une opération structurelle.
     *
     * @return array{
     *     department_id: int,
     *     dependencies: list<array{key: string, label: string, count: int}>,
     *     blockers: list<array{key: string, label: string, count: int}>,
     *     can_deactivate: bool
     * }
     */
    public function getImpact(Department $department): array
    {
        $ids = $this->subtreeIds($department);
        $dependencies = [
            ['key' => 'children', 'label' => __('Enfants actifs'),
                'count' => $this->countActiveChildren($department)],
            ['key' => 'users', 'label' => __('Utilisateurs rattachés'),
                'count' => $this->countByDepartment('UserDepartments', $ids)],
            ['key' => 'applicationforms', 'label' => __('Formulaires actifs'),
                'count' => $this->countActiveByDepartment('Applicationforms', $ids)],
            ['key' => 'validationsequences', 'label' => __('Séquences de validation actives'),
                'count' => $this->countActiveByDepartment('Validationsequences', $ids)],
            ['key' => 'cgr_codes', 'label' => __('Codes CGR actifs'),
                'count' => $this->countActiveCgrCodes($ids)],
            ['key' => 'default_cgr', 'label' => __('Départements utilisant un CGR par défaut'),
                'count' => $this->countDefaultCgrs($ids)],
        ];
        $blockers = array_values(array_filter($dependencies, static fn(array $item): bool => $item['count'] > 0));

        return [
            'department_id' => (int)$department->get('id'),
            'dependencies' => $dependencies,
            'blockers' => $blockers,
            'can_deactivate' => $blockers === [],
        ];
    }

    /** Vérifie qu’un nouveau parent ne crée pas de cycle. */
    public function assertParentChangeAllowed(Department $department, ?int $parentId): void
    {
        if ($parentId === null) {
            return;
        }
        if ($parentId === (int)$department->get('id')) {
            throw new DomainException(__('Un département ne peut pas être son propre parent.'));
        }

        $parent = $this->table()->find()->where(['id' => $parentId, 'deleted IS' => null])->first();
        if ($parent === null) {
            throw new DomainException(__('Le département parent sélectionné est introuvable ou désactivé.'));
        }
        if (
            $department->get('lft') !== null
            && $department->get('rght') !== null
            && $parent->get('lft') >= $department->get('lft')
            && $parent->get('rght') <= $department->get('rght')
        ) {
            throw new DomainException(__('Un département ne peut pas devenir le parent de son propre sous-arbre.'));
        }
    }

    /**
     * Enregistre une modification de département dans une transaction.
     *
     * @param array<string, mixed> $data
     */
    public function save(Department $department, array $data): Department
    {
        $connection = $this->table()->getConnection();

        return $connection->transactional(function () use ($department, $data): Department {
            $parentId = array_key_exists('parent_id', $data) && $data['parent_id'] !== ''
                ? (int)$data['parent_id']
                : null;
            $currentParentId = $department->get('parent_id') === null ? null : (int)$department->get('parent_id');
            if ($parentId !== $currentParentId) {
                $this->assertParentChangeAllowed($department, $parentId);
                if ($this->hasOperationalDependencies($department)) {
                    throw new DomainException(__(
                        'Le déplacement est bloqué : ce département possède des dépendances opérationnelles actives.',
                    ));
                }
            }
            if (
                array_key_exists('cgr_strategy_id', $data)
                && (int)($data['cgr_strategy_id'] ?: 0) !== (int)($department->get('cgr_strategy_id') ?: 0)
                && $this->countActiveCgrCodes($this->subtreeIds($department)) > 0
            ) {
                throw new DomainException(__(
                    'Le changement de stratégie est bloqué tant que des codes CGR actifs sont rattachés.',
                ));
            }

            $filtered = array_intersect_key($data, array_flip(self::EDITABLE_FIELDS));
            if ($department->isNew()) {
                $department->set('base', false);
            }
            $entity = $this->table()->patchEntity($department, $filtered);
            $entity->set('parent_id', $parentId);
            $this->table()->save($entity);

            return $entity;
        });
    }

    /** Désactive un département sans le supprimer physiquement. */
    public function deactivate(Department $department): Department
    {
        $impact = $this->getImpact($department);
        if (!$impact['can_deactivate']) {
            throw new DomainException(__('La désactivation est bloquée par des dépendances actives.'));
        }

        $department->set('deleted', DateTime::now());

        return $this->table()->saveOrFail($department);
    }

    /** Retourne la table des départements, éventuellement injectée. */
    private function table(): DepartmentsTable
    {
        if ($this->departments !== null) {
            return $this->departments;
        }

        /** @var \App\Model\Table\DepartmentsTable $table */
        $table = TableRegistry::getTableLocator()->get('Departments');

        return $table;
    }

    /** @return list<int> */
    private function subtreeIds(Department $department): array
    {
        $lft = $department->get('lft');
        $rght = $department->get('rght');
        if ($lft === null || $rght === null) {
            return [(int)$department->get('id')];
        }

        $items = $this->table()->find()->select(['id'])->where([
            'lft >=' => $lft,
            'rght <=' => $rght,
            'deleted IS' => null,
        ])->all()->toList();
        $ids = [];
        foreach ($items as $item) {
            if ($item instanceof Department) {
                $ids[] = (int)$item->get('id');
            }
        }

        return $ids;
    }

    /** Compte les enfants actifs directs d'un département. */
    private function countActiveChildren(Department $department): int
    {
        return $this->table()->find()->where([
            'parent_id' => $department->get('id'),
            'deleted IS' => null,
        ])->count();
    }

    /** @param list<int> $ids */
    private function countByDepartment(string $alias, array $ids): int
    {
        return $this->tableFor($alias)->find()->where(['department_id IN' => $ids])->count();
    }

    /** @param list<int> $ids */
    private function countActiveByDepartment(string $alias, array $ids): int
    {
        $table = $this->tableFor($alias);
        $query = $table->find()->where(['department_id IN' => $ids]);
        if ($table->getSchema()->hasColumn('deleted')) {
            $query->where(['deleted IS' => null]);
        }

        return $query->count();
    }

    /** @param list<int> $ids */
    private function countActiveCgrCodes(array $ids): int
    {
        return $this->tableFor('CgrCodes')->find()->where([
            'department_id IN' => $ids,
            'active' => true,
        ])->count();
    }

    /** @param list<int> $ids */
    private function countDefaultCgrs(array $ids): int
    {
        $codes = $this->tableFor('CgrCodes')->find()->select(['id'])->where([
            'department_id IN' => $ids,
        ])->all()->extract('id')->toList();
        if ($codes === []) {
            return 0;
        }

        return $this->table()->find()->where(['cgr_code_id IN' => $codes, 'deleted IS' => null])->count();
    }

    /** Indique si une mutation structurelle toucherait des données opérationnelles. */
    private function hasOperationalDependencies(Department $department): bool
    {
        $impact = $this->getImpact($department);

        return (bool)array_filter($impact['blockers'], static fn(array $item): bool => in_array(
            $item['key'],
            ['users', 'applicationforms', 'validationsequences'],
            true,
        ));
    }

    /** Retourne une table de dépendance par son alias CakePHP. */
    private function tableFor(string $alias): Table
    {
        return TableRegistry::getTableLocator()->get($alias);
    }
}
