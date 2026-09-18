<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\User;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\TableRegistry;
use Traversable;

/**
 * Departments Model
 *
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\DepartmentsTable> $ParentDepartments
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\CgrCodesTable> $DefaultCgrCode
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\CgrStrategiesTable> $CgrStrategies
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\DepartmentsTable> $ChildDepartments
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\CgrCodesTable> $OwnedCgrCodes
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\ApplicationformsTable> $Applicationforms
 * @method \App\Model\Entity\Department newEmptyEntity()
 * @method \App\Model\Entity\Department newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\App\Model\Entity\Department> newEntities(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Department get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Department findOrCreate($search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Department patchEntity(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\App\Model\Entity\Department> patchEntities(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Department|false save(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Department saveOrFail(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Department>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Department>|false saveMany(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Department>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Department> saveManyOrFail(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Department>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Department>|false deleteMany(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Department>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Department> deleteManyOrFail(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @mixin \Cake\ORM\Behavior\TreeBehavior
 */
class DepartmentsTable extends AppTable
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('departments');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Tree');

        $this->belongsTo('ParentDepartments', [
            'className' => 'Departments',
            'foreignKey' => 'parent_id',
        ]);
        $this->hasMany('ChildDepartments', [
            'className' => 'Departments',
            'foreignKey' => 'parent_id',
        ]);
        $this->belongsTo('DefaultCgrCode', [
            'className' => 'CgrCodes',
            'foreignKey' => 'cgr_code_id',
        ]);
        $this->hasMany('OwnedCgrCodes', [
            'className' => 'CgrCodes',
            'foreignKey' => 'department_id',
        ]);
        $this->belongsTo('CgrStrategies', [
            'foreignKey' => 'cgr_strategy_id',
        ]);
        $this->hasMany('Applicationforms', [
            'foreignKey' => 'department_id',
        ]);
        $this->belongsTo('Managers', [
            'className' => 'Users',
            'foreignKey' => 'current_manager_id',
            'propertyName' => 'manager',
        ]);
    }

    /**
     * Custom finder 'visibleTo' pour la table Departments.
     *
     * @param \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> $query
     * @param \App\Model\Entity\User $user
     * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface>
     */
    public function findVisibleTo(SelectQuery $query, User $user): SelectQuery
    {
        $query = parent::findVisibleTo($query, $user);
        if ($user->get('issuperuser')) {
            return $query;
        }

        $userDepartmentsTable = TableRegistry::getTableLocator()->get('UserDepartments');
        $myDepartmentIds = $userDepartmentsTable->find('departmentsOf', user: $user);

        // Fallback : Si l'utilisateur n'a aucun département rattaché, on lui laisse voir la racine pour éviter de bloquer l'IHM
        return $query->where([
            'OR' => [
                'Departments.id IN' => $myDepartmentIds,
                'Departments.parent_id IS' => null,
            ],
        ]);
    }

    /**
     * Custom finder : Récupère la structure hiérarchique imbriquée
     *
     * @param \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> $query
     * @param \App\Model\Entity\User $user
     * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface>
     */
    public function findTreeThreadedVisibleTo(SelectQuery $query, User $user): SelectQuery
    {
        return $this->find('visibleTo', user: $user)
            ->find('threaded')
            ->orderBy(['Departments.lft' => 'ASC']);
    }

    /**
     * Récupère l'arbre des départements autorisés et le formate pour TreeselectJS.
     *
     * @param \App\Model\Entity\User $user
     * @return list<array{value: int, name: string, children?: list<array<string, mixed>>}> Structure [{value, name, children}, ...]
     */
    public function findTreeSelectFormat(User $user): array
    {
        /** @var iterable<\App\Model\Entity\Department> $nodes */
        $nodes = $this->find('treeThreadedVisibleTo', user: $user)->all();

        return $this->formatForTreeSelect($nodes);
    }

    /**
     * Formate récursivement la collection d'entités en tableau compatible TreeselectJS.
     *
     * @param iterable<\App\Model\Entity\Department> $nodes
     * @return list<array{value: int, name: string, children?: list<array<string, mixed>>}>
     */
    protected function formatForTreeSelect(iterable $nodes): array
    {
        $result = [];
        foreach ($nodes as $node) {
            $item = [
                'value' => (int)$node->id,
                'name' => (string)($node->name ?? $node->code ?? 'Département #' . $node->id),
            ];

            $children = $node->get('children');
            if (!empty($children) && (is_array($children) || $children instanceof Traversable)) {
                $formattedChildren = $this->formatForTreeSelect($children);
                if (!empty($formattedChildren)) {
                    $item['children'] = $formattedChildren;
                }
            }

            $result[] = $item;
        }

        return $result;
    }
}
