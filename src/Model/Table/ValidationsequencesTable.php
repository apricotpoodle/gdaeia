<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Validationsequences Model
 *
 * @property \App\Model\Table\DepartmentsTable&\Cake\ORM\Association\BelongsTo $Departments
 * @property \App\Model\Table\RolesTable&\Cake\ORM\Association\BelongsTo $Roles
 * @property \App\Model\Table\ApplicationvalidationstepsTable&\Cake\ORM\Association\HasMany $Applicationvalidationsteps
 *
 * @method \App\Model\Entity\Validationsequence newEmptyEntity()
 * @method \App\Model\Entity\Validationsequence newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Validationsequence> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Validationsequence get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Validationsequence findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Validationsequence patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Validationsequence> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Validationsequence|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Validationsequence saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Validationsequence>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Validationsequence>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Validationsequence>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Validationsequence> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Validationsequence>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Validationsequence>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Validationsequence>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Validationsequence> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ValidationsequencesTable extends Table
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

        $this->setTable('validationsequences');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Departments', [
            'foreignKey' => 'department_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Roles', [
            'foreignKey' => 'role_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('Applicationvalidationsteps', [
            'foreignKey' => 'validationsequence_id',
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
            ->nonNegativeInteger('department_id')
            ->notEmptyString('department_id');

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->notEmptyString('name');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->nonNegativeInteger('role_id')
            ->notEmptyString('role_id');

        $validator
            ->integer('sequence')
            ->notEmptyString('sequence');

        $validator
            ->dateTime('deleted')
            ->allowEmptyDateTime('deleted');

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
        $rules->add($rules->isUnique(['department_id', 'role_id']), ['errorField' => 'department_id', 'message' => __('This combination of department_id and role_id already exists')]);
        $rules->add($rules->existsIn(['department_id'], 'Departments'), ['errorField' => 'department_id']);
        $rules->add($rules->existsIn(['role_id'], 'Roles'), ['errorField' => 'role_id']);

        return $rules;
    }
}
