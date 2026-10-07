<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Worktime;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * Worktimes Model
 *
 * @property \App\Model\Table\ApplicationformsTable $Applicationforms
 * @method \App\Model\Entity\Worktime newEmptyEntity()
 * @method \App\Model\Entity\Worktime newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method list<\App\Model\Entity\Worktime> newEntities(list<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Worktime get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Worktime findOrCreate(mixed $search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Worktime patchEntity(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method list<\App\Model\Entity\Worktime> patchEntities(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Worktime|false save(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Worktime saveOrFail(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Worktime>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Worktime>|false saveMany(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Worktime>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Worktime> saveManyOrFail(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Worktime>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Worktime>|false deleteMany(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Worktime>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Worktime> deleteManyOrFail(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class WorktimesTable extends AppTable
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

        $this->setTable('worktimes');
        $this->setDisplayField(Worktime::FIELD_NAME);
        $this->setPrimaryKey(Worktime::FIELD_ID);

        $this->addBehavior('Timestamp');

        $this->hasMany('Applicationforms', [
            'foreignKey' => 'worktime_id',
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
            ->boolean(Worktime::FIELD_BASE)
            ->notEmptyString(Worktime::FIELD_BASE);

        $validator
            ->scalar(Worktime::FIELD_CODE)
            ->maxLength(Worktime::FIELD_CODE, 16)
            ->requirePresence(Worktime::FIELD_CODE, 'create')
            ->notEmptyString(Worktime::FIELD_CODE)
            /** @link validateUnique() */
            ->add(Worktime::FIELD_CODE, 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar(Worktime::FIELD_NAME)
            ->maxLength(Worktime::FIELD_NAME, 32)
            ->requirePresence(Worktime::FIELD_NAME, 'create')
            ->notEmptyString(Worktime::FIELD_NAME)
            /** @link validateUnique() */
            ->add(Worktime::FIELD_NAME, 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar(Worktime::FIELD_SORT)
            ->maxLength(Worktime::FIELD_SORT, 32)
            ->notEmptyString(Worktime::FIELD_SORT);

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
        $rules->add($rules->isUnique([Worktime::FIELD_CODE]), ['errorField' => Worktime::FIELD_CODE]);
        $rules->add($rules->isUnique([Worktime::FIELD_NAME]), ['errorField' => Worktime::FIELD_NAME]);

        return $rules;
    }
}
