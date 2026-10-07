<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Period;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * Periods Model
 *
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\ApplicationformsTable> $Applicationforms
 * @method \App\Model\Entity\Period newEmptyEntity()
 * @method \App\Model\Entity\Period newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\App\Model\Entity\Period> newEntities(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Period get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Period findOrCreate($search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Period patchEntity(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\App\Model\Entity\Period> patchEntities(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Period|false save(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Period saveOrFail(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Period>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Period>|false saveMany(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Period>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Period> saveManyOrFail(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Period>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Period>|false deleteMany(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Period>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Period> deleteManyOrFail(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class PeriodsTable extends AppTable
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

        $this->setTable('periods');
        $this->setDisplayField(Period::FIELD_NAME);
        $this->setPrimaryKey(Period::FIELD_ID);

        $this->addBehavior('Timestamp');

        $this->hasMany('Applicationforms', [
            'foreignKey' => 'period_id',
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
            ->boolean(Period::FIELD_BASE)
            ->notEmptyString(Period::FIELD_BASE);

        $validator
            ->scalar(Period::FIELD_CODE)
            ->maxLength(Period::FIELD_CODE, 16)
            ->requirePresence(Period::FIELD_CODE, 'create')
            ->notEmptyString(Period::FIELD_CODE)
            /** @link validateUnique() */
            ->add(Period::FIELD_CODE, 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar(Period::FIELD_NAME)
            ->maxLength(Period::FIELD_NAME, 32)
            ->requirePresence(Period::FIELD_NAME, 'create')
            ->notEmptyString(Period::FIELD_NAME)
            /** @link validateUnique() */
            ->add(Period::FIELD_NAME, 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar(Period::FIELD_SORT)
            ->maxLength(Period::FIELD_SORT, 32)
            ->notEmptyString(Period::FIELD_SORT);

        $validator
            ->dateTime(Period::FIELD_DELETED)
            ->allowEmptyDateTime(Period::FIELD_DELETED);

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
        $rules->add($rules->isUnique([Period::FIELD_CODE]), ['errorField' => Period::FIELD_CODE]);
        $rules->add($rules->isUnique([Period::FIELD_NAME]), ['errorField' => Period::FIELD_NAME]);

        return $rules;
    }
}
