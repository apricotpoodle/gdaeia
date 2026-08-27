<?php

declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\User;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * Departments Model
 *
 * @property \App\Model\Table\DepartmentsTable&\Cake\ORM\Association\BelongsTo $ParentDepartments
 * @property \App\Model\Table\CgrCodesTable&\Cake\ORM\Association\BelongsTo $DefaultCgrCode
 * @property \App\Model\Table\CgrStrategiesTable&\Cake\ORM\Association\BelongsTo $CgrStrategies
 * @property \App\Model\Table\DepartmentsTable&\Cake\ORM\Association\HasMany $ChildDepartments
 * @property \App\Model\Table\CgrCodesTable&\Cake\ORM\Association\HasMany $OwnedCgrCodes
 * @property \App\Model\Table\ApplicationformsTable&\Cake\ORM\Association\HasMany $Applicationforms
 * @method \App\Model\Entity\Department newEmptyEntity()
 * @method \App\Model\Entity\Department newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Department> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Department get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Department findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Department patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Department> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Department|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Department saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Department>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Department>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Department>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Department> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Department>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Department>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Department>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Department> deleteManyOrFail(iterable $entities, array $options = [])
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
     * @param \Cake\ORM\Query\SelectQuery $query
     * @param \App\Model\Entity\User $user
     * @return \Cake\ORM\Query\SelectQuery
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
                'Departments.parent_id IS' => null
            ]
        ]);
    }

    /**
     * Custom finder : Récupère la structure hiérarchique imbriquée
     *
     * @param \Cake\ORM\Query\SelectQuery $query
     * @param \App\Model\Entity\User $user
     * @return \Cake\ORM\Query\SelectQuery
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
     * @return array Structure [{value, name, children}, ...]
     */
    public function findTreeSelectFormat(User $user): array
    {
        $nodes = $this->find('treeThreadedVisibleTo', user: $user)->all();

        return $this->formatForTreeSelect($nodes);
    }

    /**
     * Formate récursivement la collection d'entités en tableau compatible TreeselectJS.
     *
     * @param iterable $nodes
     * @return array
     */
    protected function formatForTreeSelect(iterable $nodes): array
    {
        $result = [];
        foreach ($nodes as $node) {
            $item = [
                'value' => (int)$node->id,
                'name' => (string)($node->name ?? $node->code ?? ('Département #' . $node->id)),
            ];

            $children = $node->get('children');
            if (!empty($children) && (is_array($children) || $children instanceof \Traversable)) {
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
