<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Budgetfeature;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * Budgetfeatures Model
 *
 * @property \App\Model\Table\ApplicationformsTable $Applicationforms
 * @method \App\Model\Entity\Budgetfeature newEmptyEntity()
 * @method \App\Model\Entity\Budgetfeature newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method list<\App\Model\Entity\Budgetfeature> newEntities(list<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Budgetfeature get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Budgetfeature findOrCreate(mixed $search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Budgetfeature patchEntity(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method list<\App\Model\Entity\Budgetfeature> patchEntities(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Budgetfeature|false save(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Budgetfeature saveOrFail(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Budgetfeature>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Budgetfeature>|false saveMany(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Budgetfeature>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Budgetfeature> saveManyOrFail(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Budgetfeature>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Budgetfeature>|false deleteMany(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Budgetfeature>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Budgetfeature> deleteManyOrFail(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class BudgetfeaturesTable extends AppTable
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

        $this->setTable('budgetfeatures');
        $this->setDisplayField(Budgetfeature::FIELD_NAME);
        $this->setPrimaryKey(Budgetfeature::FIELD_ID);

        $this->addBehavior('Timestamp');

        $this->hasMany('Applicationforms', [
            'foreignKey' => 'budgetfeature_id',
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
            ->boolean(Budgetfeature::FIELD_BASE)
            ->notEmptyString(Budgetfeature::FIELD_BASE);

        $validator
            ->scalar(Budgetfeature::FIELD_CODE)
            ->maxLength(Budgetfeature::FIELD_CODE, 16)
            ->requirePresence(Budgetfeature::FIELD_CODE, 'create')
            ->notEmptyString(Budgetfeature::FIELD_CODE)
            /** @link validateUnique() */
            ->add(Budgetfeature::FIELD_CODE, 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar(Budgetfeature::FIELD_NAME)
            ->maxLength(Budgetfeature::FIELD_NAME, 32)
            ->requirePresence(Budgetfeature::FIELD_NAME, 'create')
            ->notEmptyString(Budgetfeature::FIELD_NAME)
            /** @link validateUnique() */
            ->add(Budgetfeature::FIELD_NAME, 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar(Budgetfeature::FIELD_SORT)
            ->maxLength(Budgetfeature::FIELD_SORT, 32)
            ->notEmptyString(Budgetfeature::FIELD_SORT);

        $validator
            ->dateTime(Budgetfeature::FIELD_DELETED)
            ->allowEmptyDateTime(Budgetfeature::FIELD_DELETED);

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
        $rules->add($rules->isUnique([Budgetfeature::FIELD_CODE]), ['errorField' => Budgetfeature::FIELD_CODE]);
        $rules->add($rules->isUnique([Budgetfeature::FIELD_NAME]), ['errorField' => Budgetfeature::FIELD_NAME]);

        return $rules;
    }
}
