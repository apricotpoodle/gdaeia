<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CgrCodes Model
 *
 * @property \App\Model\Table\DepartmentsTable&\Cake\ORM\Association\BelongsTo $Departments
 * @property \App\Model\Table\CgrCodesTable&\Cake\ORM\Association\HasMany $CgrCodes
 *
 * @method \App\Model\Entity\CgrCode newEmptyEntity()
 * @method \App\Model\Entity\CgrCode newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\CgrCode> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CgrCode get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\CgrCode findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\CgrCode patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\CgrCode> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\CgrCode|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\CgrCode saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\CgrCode>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CgrCode>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CgrCode>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CgrCode> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CgrCode>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CgrCode>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CgrCode>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CgrCode> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CgrCodesTable extends Table
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

        $this->setTable('cgr_codes');
        $this->setDisplayField('label');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('OwnerDepartments', [
            'className' => 'Departments',
            'foreignKey' => 'department_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('UsingDepartments', [
            'foreignKey' => 'cgr_code_id',
            'className' => 'Departments',
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
            ->scalar('type')
            ->maxLength('type', 32)
            ->requirePresence('type', 'create')
            ->notEmptyString('type');

        $validator
            ->scalar('code')
            ->maxLength('code', 16)
            ->requirePresence('code', 'create')
            ->notEmptyString('code');

        $validator
            ->scalar('label')
            ->maxLength('label', 255)
            ->requirePresence('label', 'create')
            ->notEmptyString('label');

        $validator
            ->boolean('active')
            ->notEmptyString('active');

        $validator
            ->boolean('is_system')
            ->notEmptyString('is_system');

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
        $rules->add($rules->isUnique(['department_id', 'type', 'code']), ['errorField' => 'department_id', 'message' => __('This combination of department_id, type and code already exists')]);
        $rules->add($rules->existsIn(['department_id'], 'Departments'), ['errorField' => 'department_id']);

        return $rules;
    }
}
