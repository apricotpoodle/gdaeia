<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Yesno;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * Yesnos Model
 *
 * @property \App\Model\Table\ApplicationformsTable $Applicationforms
 * @method \App\Model\Entity\Yesno newEmptyEntity()
 * @method \App\Model\Entity\Yesno newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method list<\App\Model\Entity\Yesno> newEntities(list<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Yesno get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Yesno findOrCreate(mixed $search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Yesno patchEntity(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method list<\App\Model\Entity\Yesno> patchEntities(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Yesno|false save(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Yesno saveOrFail(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Yesno>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Yesno>|false saveMany(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Yesno>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Yesno> saveManyOrFail(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Yesno>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Yesno>|false deleteMany(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Yesno>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Yesno> deleteManyOrFail(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class YesnosTable extends AppTable
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

        $this->setTable('yesnos');
        $this->setDisplayField(Yesno::FIELD_NAME);
        $this->setPrimaryKey(Yesno::FIELD_ID);

        $this->addBehavior('Timestamp');

        $this->hasMany('Applicationforms', [
            'foreignKey' => 'yesno_id',
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
            ->boolean(Yesno::FIELD_BASE)
            ->notEmptyString(Yesno::FIELD_BASE);

        $validator
            ->scalar(Yesno::FIELD_CODE)
            ->maxLength(Yesno::FIELD_CODE, 16)
            ->requirePresence(Yesno::FIELD_CODE, 'create')
            ->notEmptyString(Yesno::FIELD_CODE)
            /** @link validateUnique() */
            ->add(Yesno::FIELD_CODE, 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar(Yesno::FIELD_NAME)
            ->maxLength(Yesno::FIELD_NAME, 32)
            ->requirePresence(Yesno::FIELD_NAME, 'create')
            ->notEmptyString(Yesno::FIELD_NAME)
            /** @link validateUnique() */
            ->add(Yesno::FIELD_NAME, 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar(Yesno::FIELD_SORT)
            ->maxLength(Yesno::FIELD_SORT, 32)
            ->notEmptyString(Yesno::FIELD_SORT);

        $validator
            ->dateTime(Yesno::FIELD_DELETED)
            ->allowEmptyDateTime(Yesno::FIELD_DELETED);

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
        $rules->add($rules->isUnique([Yesno::FIELD_CODE]), ['errorField' => Yesno::FIELD_CODE]);
        $rules->add($rules->isUnique([Yesno::FIELD_NAME]), ['errorField' => Yesno::FIELD_NAME]);

        return $rules;
    }
}
