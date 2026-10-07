<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Hiringreason;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * Hiringreasons Model
 *
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\ApplicationformsTable> $Applicationforms
 * @method \App\Model\Entity\Hiringreason newEmptyEntity()
 * @method \App\Model\Entity\Hiringreason newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\App\Model\Entity\Hiringreason> newEntities(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Hiringreason get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Hiringreason findOrCreate($search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Hiringreason patchEntity(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\App\Model\Entity\Hiringreason> patchEntities(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Hiringreason|false save(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Hiringreason saveOrFail(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Hiringreason>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Hiringreason>|false saveMany(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Hiringreason>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Hiringreason> saveManyOrFail(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Hiringreason>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Hiringreason>|false deleteMany(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Hiringreason>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Hiringreason> deleteManyOrFail(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class HiringreasonsTable extends AppTable
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

        $this->setTable('hiringreasons');
        $this->setDisplayField(Hiringreason::FIELD_NAME);
        $this->setPrimaryKey(Hiringreason::FIELD_ID);

        $this->addBehavior('Timestamp');

        $this->hasMany('Applicationforms', [
            'foreignKey' => 'hiringreason_id',
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
            ->boolean(Hiringreason::FIELD_BASE)
            ->notEmptyString(Hiringreason::FIELD_BASE);

        $validator
            ->scalar(Hiringreason::FIELD_CODE)
            ->maxLength(Hiringreason::FIELD_CODE, 16)
            ->requirePresence(Hiringreason::FIELD_CODE, 'create')
            ->notEmptyString(Hiringreason::FIELD_CODE)
            /** @link validateUnique() */
            ->add(Hiringreason::FIELD_CODE, 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar(Hiringreason::FIELD_NAME)
            ->maxLength(Hiringreason::FIELD_NAME, 32)
            ->requirePresence(Hiringreason::FIELD_NAME, 'create')
            ->notEmptyString(Hiringreason::FIELD_NAME)
            /** @link validateUnique() */
            ->add(Hiringreason::FIELD_NAME, 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar(Hiringreason::FIELD_SORT)
            ->maxLength(Hiringreason::FIELD_SORT, 32)
            ->notEmptyString(Hiringreason::FIELD_SORT);

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
        $rules->add($rules->isUnique([Hiringreason::FIELD_CODE]), ['errorField' => Hiringreason::FIELD_CODE]);
        $rules->add($rules->isUnique([Hiringreason::FIELD_NAME]), ['errorField' => Hiringreason::FIELD_NAME]);

        return $rules;
    }
}
