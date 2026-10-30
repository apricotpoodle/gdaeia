<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\Department;
use App\Model\Entity\User;
use App\Model\Table\DepartmentsTable;
use Cake\I18n\DateTime;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use DomainException;

/** Centralise les règles d’administration de l’arbre des départements. */
final class DepartmentManagementService
{
    /** @var list<string> */
    public const CLEANUP_KEYS = [
        'children',
        'users',
        'validationsequences',
        'cgr_codes',
    ];

    /** @var list<string> */
    private const EDITABLE_FIELDS = [
        'parent_id', 'code', 'name', 'sort', 'department_type_id',
        'cgr_strategy_id', 'base',
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
            ['key' => 'cgr_system', 'label' => __('Codes CGR système actifs'),
                'count' => $this->countActiveSystemCgrCodes($ids)],
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
            if ($entity->isNew()) {
                $entity->set('base', false);
            }
            $entity->set('parent_id', $parentId);
            $this->table()->save($entity);
            if (array_key_exists('manager_ids', $data)) {
                $this->replaceAssociation($entity, 'Managers', $this->associationIds($data['manager_ids']));
            }
            if (array_key_exists('cgr_code_ids', $data)) {
                $this->replaceAssociation($entity, 'CgrCodes', $this->associationIds($data['cgr_code_ids']));
            }

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

    /**
     * Nettoie les dépendances sélectionnées puis désactive le département.
     *
     * Les formulaires actifs et les codes CGR système restent toujours
     * bloquants. Les comptes utilisateurs ne sont jamais désactivés.
     *
     * @param list<string> $cleanup Catégories explicitement confirmées.
     * @return array{
     *     department_ids: list<int>,
     *     detached_user_ids: list<int>,
     *     deactivated_sequence_ids: list<int>,
     *     deactivated_cgr_ids: list<int>,
     * }
     */
    public function deactivateWithCleanup(Department $department, array $cleanup): array
    {
        $unknownKeys = array_diff($cleanup, self::CLEANUP_KEYS);
        if ($unknownKeys !== []) {
            throw new DomainException(__('Une catégorie de nettoyage est invalide.'));
        }
        if ($department->get('deleted') !== null) {
            throw new DomainException(__('Le département est déjà désactivé.'));
        }
        if ((bool)$department->get('base')) {
            throw new DomainException(__('Un département socle ne peut pas être désactivé.'));
        }

        $cleanup = array_values(array_unique($cleanup));
        $impact = $this->getImpact($department);
        $impactByKey = [];
        foreach ($impact['dependencies'] as $dependency) {
            $impactByKey[$dependency['key']] = $dependency['count'];
        }
        if (($impactByKey['applicationforms'] ?? 0) > 0) {
            throw new DomainException(__('La désactivation reste bloquée : des formulaires actifs existent.'));
        }
        if (($impactByKey['cgr_system'] ?? 0) > 0) {
            throw new DomainException(__('La désactivation reste bloquée : des codes CGR système actifs existent.'));
        }
        foreach (['children', 'users', 'validationsequences', 'cgr_codes'] as $key) {
            if (($impactByKey[$key] ?? 0) > 0 && !in_array($key, $cleanup, true)) {
                throw new DomainException(__(
                    'La désactivation reste bloquée : la dépendance « {0} » doit être confirmée.',
                    $key,
                ));
            }
        }

        $departmentIds = $this->subtreeIds($department);
        if (!in_array('children', $cleanup, true)) {
            $departmentIds = [(int)$department->get('id')];
        }
        $now = DateTime::now();

        return $this->table()->getConnection()->transactional(
            function () use ($cleanup, $departmentIds, $now): array {
                $detachedUserIds = [];
                $deactivatedSequenceIds = [];
                $deactivatedCgrIds = [];

                if (in_array('users', $cleanup, true)) {
                    $userDepartments = $this->tableFor('UserDepartments');
                    $detachedUserIds = $userDepartments->find()
                        ->select(['user_id'])
                        ->where(['department_id IN' => $departmentIds])
                        ->all()
                        ->extract('user_id')
                        ->toList();
                    $userDepartments->deleteAll(['department_id IN' => $departmentIds]);
                }

                if (in_array('validationsequences', $cleanup, true)) {
                    $sequences = $this->tableFor('Validationsequences');
                    $deactivatedSequenceIds = $sequences->find()
                        ->select(['id'])
                        ->where([
                            'department_id IN' => $departmentIds,
                            'deleted IS' => null,
                        ])
                        ->all()
                        ->extract('id')
                        ->toList();
                    $sequences->updateAll(['deleted' => $now], [
                        'department_id IN' => $departmentIds,
                        'deleted IS' => null,
                    ]);
                }

                if (in_array('cgr_codes', $cleanup, true)) {
                    $cgrCodes = $this->tableFor('CgrCodes');
                    $deactivatedCgrIds = $cgrCodes->find()
                        ->select(['id'])
                        ->matching('Departments', static function ($query) use ($departmentIds) {
                            return $query->where(['Departments.id IN' => $departmentIds]);
                        })
                        ->where([
                            'active' => true,
                            'is_system' => false,
                        ])
                        ->all()
                        ->extract('id')
                        ->toList();
                    if ($deactivatedCgrIds !== []) {
                        $cgrCodes->updateAll(['active' => false], [
                            'id IN' => $deactivatedCgrIds,
                        ]);
                    }
                }

                $this->table()->updateAll(['deleted' => $now], [
                    'id IN' => $departmentIds,
                    'deleted IS' => null,
                ]);

                return [
                    'department_ids' => $departmentIds,
                    'detached_user_ids' => array_map('intval', $detachedUserIds),
                    'deactivated_sequence_ids' => array_map('intval', $deactivatedSequenceIds),
                    'deactivated_cgr_ids' => array_map('intval', $deactivatedCgrIds),
                ];
            },
        );
    }

    /** @return list<\App\Model\Entity\User> */
    public function notificationRecipients(Department $department, User $actor): array
    {
        $departmentIds = $this->subtreeIds($department);
        $userIds = [(int)$actor->get('id')];
        $managerIds = $this->tableFor('DepartmentManagers')->find()
            ->select(['user_id'])
            ->where(['department_id IN' => $departmentIds])
            ->all()
            ->extract('user_id')
            ->toList();
        $userIds = array_merge($userIds, array_map('intval', $managerIds));

        $userDepartments = $this->tableFor('UserDepartments');
        $assignedUserIds = $userDepartments->find()
            ->select(['user_id'])
            ->where(['department_id IN' => $departmentIds])
            ->all()
            ->extract('user_id')
            ->toList();
        $userIds = array_merge($userIds, array_map('intval', $assignedUserIds));

        $administrators = $this->tableFor('Users')->find()
            ->innerJoinWith('UserDepartments', static function ($query) use ($departmentIds) {
                return $query->where(['UserDepartments.department_id IN' => $departmentIds]);
            })
            ->select(['Users.id'])
            ->where([
                'Users.role_id' => User::ROLE_ADMIN,
                'Users.deleted IS' => null,
            ])
            ->all()
            ->extract('id')
            ->toList();
        $userIds = array_values(array_unique(array_merge($userIds, array_map('intval', $administrators))));

        /** @var list<\App\Model\Entity\User> $recipients */
        $recipients = $this->tableFor('Users')->find()
            ->where([
                'Users.id IN' => $userIds,
                'Users.deleted IS' => null,
            ])
            ->all()
            ->toList();
        if (!$recipients && $actor->get('email')) {
            return [$actor];
        }

        return $recipients;
    }

    /**
     * Retourne les destinataires autorisés à recevoir les identifiants détaillés.
     *
     * @return list<int>
     */
    public function fullReportRecipientIds(Department $department, User $actor): array
    {
        $departmentIds = $this->subtreeIds($department);
        $managerIds = $this->tableFor('DepartmentManagers')->find()
            ->select(['user_id'])
            ->where(['department_id IN' => $departmentIds])
            ->all()
            ->extract('user_id')
            ->toList();
        $administratorIds = $this->tableFor('Users')->find()
            ->innerJoinWith('UserDepartments', static function ($query) use ($departmentIds) {
                return $query->where(['UserDepartments.department_id IN' => $departmentIds]);
            })
            ->select(['Users.id'])
            ->where([
                'Users.role_id' => User::ROLE_ADMIN,
                'Users.deleted IS' => null,
            ])
            ->all()
            ->extract('id')
            ->toList();

        return array_values(array_unique(array_map(
            'intval',
            array_merge([(int)$actor->get('id')], $managerIds, $administratorIds),
        )));
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
        return $this->tableFor('CgrCodes')->find()
            ->matching('Departments', static function ($query) use ($ids) {
                return $query->where(['Departments.id IN' => $ids]);
            })
            ->where([
            'active' => true,
        ])->count();
    }

    /** @param list<int> $ids */
    private function countActiveSystemCgrCodes(array $ids): int
    {
        return $this->tableFor('CgrCodes')->find()
            ->matching('Departments', static function ($query) use ($ids) {
                return $query->where(['Departments.id IN' => $ids]);
            })
            ->where([
                'active' => true,
                'is_system' => true,
            ])->count();
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

    /**
     * Remplace une collection Many-to-Many sans supprimer les entités cibles.
     *
     * @param \App\Model\Entity\Department $department Département propriétaire.
     * @param string $association Alias de l'association.
     * @param list<int> $ids Identifiants demandés.
     */
    private function replaceAssociation(Department $department, string $association, array $ids): void
    {
        /** @var \Cake\ORM\Association\BelongsToMany<\Cake\ORM\Table> $relation */
        $relation = $this->table()->getAssociation($association);
        $target = $relation->getTarget();
        $entities = $ids === []
            ? []
            : $target->find()->where(['id IN' => $ids])->all()->toList();
        if (count($entities) !== count($ids)) {
            throw new DomainException(__('Une association sélectionnée est introuvable.'));
        }
        $relation->replaceLinks($department, $entities);
    }

    /** @return list<int> */
    private function associationIds(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $ids = [];
        foreach ($value as $item) {
            if (is_int($item) || (is_string($item) && ctype_digit($item))) {
                $id = (int)$item;
                if ($id > 0) {
                    $ids[$id] = $id;
                }
            }
        }

        return array_values($ids);
    }
}
