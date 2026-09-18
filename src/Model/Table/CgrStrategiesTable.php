<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CgrStrategies Model
 *
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\DepartmentsTable> $Departments
 * @method \App\Model\Entity\CgrStrategy newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\CgrStrategy[] newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\CgrStrategy get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\CgrStrategy findOrCreate(\Cake\ORM\Query\SelectQuery<\App\Model\Entity\CgrStrategy>|callable|array<string, mixed> $search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\CgrStrategy patchEntity(\App\Model\Entity\CgrStrategy $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\CgrStrategy[] patchEntities(iterable<\App\Model\Entity\CgrStrategy> $entities, array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\CgrStrategy|false save(\App\Model\Entity\CgrStrategy $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\CgrStrategy saveOrFail(\App\Model\Entity\CgrStrategy $entity, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\CgrStrategy>|false saveMany(iterable<\App\Model\Entity\CgrStrategy> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\CgrStrategy> saveManyOrFail(iterable<\App\Model\Entity\CgrStrategy> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\CgrStrategy>|false deleteMany(iterable<\App\Model\Entity\CgrStrategy> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\CgrStrategy> deleteManyOrFail(iterable<\App\Model\Entity\CgrStrategy> $entities, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}, \App\Model\Entity\CgrStrategy>
 * @method bool delete(\App\Model\Entity\CgrStrategy $entity, array<string, mixed> $options = [])
 * @method bool deleteOrFail(\App\Model\Entity\CgrStrategy $entity, array<string, mixed> $options = [])
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
