<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CgrStrategies Model
 *
 * @property \App\Model\Table\DepartmentsTable&\Cake\ORM\Association\HasMany $Departments
 *
 * @method \App\Model\Entity\CgrStrategy newEmptyEntity()
 * @method \App\Model\Entity\CgrStrategy newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\CgrStrategy> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CgrStrategy get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\CgrStrategy findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\CgrStrategy patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\CgrStrategy> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\CgrStrategy|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\CgrStrategy saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\CgrStrategy>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CgrStrategy>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CgrStrategy>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CgrStrategy> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CgrStrategy>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CgrStrategy>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CgrStrategy>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CgrStrategy> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CgrStrategiesTable extends Table
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

        $this->setTable('cgr_strategies');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Departments', [
            'foreignKey' => 'cgr_strategy_id',
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
            ->scalar('code')
            ->maxLength('code', 32)
            ->requirePresence('code', 'create')
            ->notEmptyString('code')
            ->add('code', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('name')
            ->maxLength('name', 64)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->requirePresence('definition_json', 'create')
            ->notEmptyString('definition_json');

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
        $rules->add($rules->isUnique(['code']), ['errorField' => 'code']);

        return $rules;
    }
}
