<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\CgrCode;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CgrCodes Model
 *
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\DepartmentsTable> $Departments
 * @method \App\Model\Entity\CgrCode newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\CgrCode[] newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\CgrCode get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\CgrCode findOrCreate(\Cake\ORM\Query\SelectQuery<\App\Model\Entity\CgrCode>|callable|array<string, mixed> $search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\CgrCode patchEntity(\App\Model\Entity\CgrCode $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\CgrCode[] patchEntities(iterable<\App\Model\Entity\CgrCode> $entities, array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\CgrCode|false save(\App\Model\Entity\CgrCode $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\CgrCode saveOrFail(\App\Model\Entity\CgrCode $entity, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\CgrCode>|false saveMany(iterable<\App\Model\Entity\CgrCode> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\CgrCode> saveManyOrFail(iterable<\App\Model\Entity\CgrCode> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\CgrCode>|false deleteMany(iterable<\App\Model\Entity\CgrCode> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\CgrCode> deleteManyOrFail(iterable<\App\Model\Entity\CgrCode> $entities, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}, \App\Model\Entity\CgrCode>
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\DepartmentsTable> $UsingDepartments
 * @method bool delete(\App\Model\Entity\CgrCode $entity, array<string, mixed> $options = [])
 * @method bool deleteOrFail(\App\Model\Entity\CgrCode $entity, array<string, mixed> $options = [])
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
        $this->setDisplayField(CgrCode::FIELD_LABEL);
        $this->setPrimaryKey(CgrCode::FIELD_ID);

        $this->addBehavior('Timestamp');

        $this->belongsTo('Departments', [
            'foreignKey' => CgrCode::FIELD_DEPARTMENT_ID,
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
            ->nonNegativeInteger(CgrCode::FIELD_DEPARTMENT_ID)
            ->notEmptyString(CgrCode::FIELD_DEPARTMENT_ID);

        $validator
            ->scalar(CgrCode::FIELD_TYPE)
            ->maxLength(CgrCode::FIELD_TYPE, 32)
            ->requirePresence(CgrCode::FIELD_TYPE, 'create')
            ->notEmptyString(CgrCode::FIELD_TYPE);

        $validator
            ->scalar(CgrCode::FIELD_CODE)
            ->maxLength(CgrCode::FIELD_CODE, 16)
            ->requirePresence(CgrCode::FIELD_CODE, 'create')
            ->notEmptyString(CgrCode::FIELD_CODE);

        $validator
            ->scalar(CgrCode::FIELD_LABEL)
            ->maxLength(CgrCode::FIELD_LABEL, 255)
            ->requirePresence(CgrCode::FIELD_LABEL, 'create')
            ->notEmptyString(CgrCode::FIELD_LABEL);

        $validator
            ->boolean(CgrCode::FIELD_ACTIVE)
            ->notEmptyString(CgrCode::FIELD_ACTIVE);

        $validator
            ->boolean(CgrCode::FIELD_IS_SYSTEM)
            ->notEmptyString(CgrCode::FIELD_IS_SYSTEM);

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
        $rules->add($rules->isUnique([CgrCode::FIELD_DEPARTMENT_ID, CgrCode::FIELD_TYPE, CgrCode::FIELD_CODE]), [
            'errorField' => CgrCode::FIELD_DEPARTMENT_ID,
            'message' => __('Cette combinaison de département, type et code existe déjà.'),
        ]);
        $rules->add($rules->existsIn([CgrCode::FIELD_DEPARTMENT_ID], 'Departments'), ['errorField' => CgrCode::FIELD_DEPARTMENT_ID]);

        return $rules;
    }
}
