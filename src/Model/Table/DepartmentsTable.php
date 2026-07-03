<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
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
 *
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
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @mixin \Cake\ORM\Behavior\TreeBehavior
 */
class DepartmentsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('departments');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        // Comportements natifs
        $this->addBehavior('Timestamp');
        $this->addBehavior('Tree'); // Gestion automatique des champs lft, rght, parent_id, level

        // Relations récursives (Arbre des départements)
        $this->belongsTo('ParentDepartments', [
            'className' => 'Departments',
            'foreignKey' => 'parent_id',
        ]);
        $this->hasMany('ChildDepartments', [
            'className' => 'Departments',
            'foreignKey' => 'parent_id',
        ]);

        // Relations complexes gérées par des alias sémantiques (La solution au crash de Bake)
        $this->belongsTo('DefaultCgrCode', [
            'className' => 'CgrCodes',
            'foreignKey' => 'cgr_code_id',
        ]);
        $this->hasMany('OwnedCgrCodes', [
            'className' => 'CgrCodes',
            'foreignKey' => 'department_id',
        ]);

        // Autres relations classiques
        $this->belongsTo('CgrStrategies', [
            'foreignKey' => 'cgr_strategy_id',
        ]);
        $this->hasMany('Applicationforms', [
            'foreignKey' => 'department_id',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->nonNegativeInteger('parent_id')
            ->allowEmptyString('parent_id');

        $validator
            ->nonNegativeInteger('cgr_code_id')
            ->allowEmptyString('cgr_code_id');

        $validator
            ->boolean('base')
            ->notEmptyString('base');

        $validator
            ->scalar('code')
            ->maxLength('code', 32)
            ->requirePresence('code', 'create')
            ->notEmptyString('code');

        $validator
            ->scalar('name')
            ->maxLength('name', 64)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('sort')
            ->maxLength('sort', 64)
            ->allowEmptyString('sort');

        $validator
            ->nonNegativeInteger('department_type_id')
            ->notEmptyString('department_type_id');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['code']), ['errorField' => 'code', 'message' => __('Ce code existe déjà.')]);
        $rules->add($rules->isUnique(['name']), ['errorField' => 'name', 'message' => __('Ce nom existe déjà.')]);
        
        $rules->add($rules->existsIn(['parent_id'], 'ParentDepartments'), ['errorField' => 'parent_id']);
        $rules->add($rules->existsIn(['cgr_code_id'], 'DefaultCgrCode'), ['errorField' => 'cgr_code_id']);
        $rules->add($rules->existsIn(['cgr_strategy_id'], 'CgrStrategies'), ['errorField' => 'cgr_strategy_id']);

        return $rules;
    }
}