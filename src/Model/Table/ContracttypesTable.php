<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Contracttype;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * Contracttypes Model
 *
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\ApplicationformsTable> $Applicationforms
 * @method \App\Model\Entity\Contracttype newEmptyEntity()
 * @method \App\Model\Entity\Contracttype newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\App\Model\Entity\Contracttype> newEntities(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Contracttype get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Contracttype findOrCreate($search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Contracttype patchEntity(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\App\Model\Entity\Contracttype> patchEntities(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Contracttype|false save(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Contracttype saveOrFail(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Contracttype>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Contracttype>|false saveMany(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Contracttype>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Contracttype> saveManyOrFail(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Contracttype>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Contracttype>|false deleteMany(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Contracttype>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Contracttype> deleteManyOrFail(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ContracttypesTable extends AppTable
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

        $this->setTable('contracttypes');
        $this->setDisplayField(Contracttype::FIELD_NAME);
        $this->setPrimaryKey(Contracttype::FIELD_ID);

        $this->addBehavior('Timestamp');

        $this->hasMany('Applicationforms', [
            'foreignKey' => 'contracttype_id',
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
            ->boolean(Contracttype::FIELD_BASE)
            ->notEmptyString(Contracttype::FIELD_BASE);

        $validator
            ->scalar(Contracttype::FIELD_CODE)
            ->maxLength(Contracttype::FIELD_CODE, 16)
            ->requirePresence(Contracttype::FIELD_CODE, 'create')
            ->notEmptyString(Contracttype::FIELD_CODE)
            /** @link validateUnique() */
            ->add(Contracttype::FIELD_CODE, 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar(Contracttype::FIELD_NAME)
            ->maxLength(Contracttype::FIELD_NAME, 32)
            ->requirePresence(Contracttype::FIELD_NAME, 'create')
            ->notEmptyString(Contracttype::FIELD_NAME)
            /** @link validateUnique() */
            ->add(Contracttype::FIELD_NAME, 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar(Contracttype::FIELD_SORT)
            ->maxLength(Contracttype::FIELD_SORT, 32)
            ->notEmptyString(Contracttype::FIELD_SORT);

        $validator
            ->dateTime(Contracttype::FIELD_DELETED)
            ->allowEmptyDateTime(Contracttype::FIELD_DELETED);

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
        $rules->add($rules->isUnique([Contracttype::FIELD_CODE]), ['errorField' => Contracttype::FIELD_CODE]);
        $rules->add($rules->isUnique([Contracttype::FIELD_NAME]), ['errorField' => Contracttype::FIELD_NAME]);

        return $rules;
    }
}
