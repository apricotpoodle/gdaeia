<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Worktimes Model
 *
 * @property \App\Model\Table\ApplicationformsTable&\Cake\ORM\Association\HasMany $Applicationforms
 *
 * @method \App\Model\Entity\Worktime newEmptyEntity()
 * @method \App\Model\Entity\Worktime newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Worktime> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Worktime get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Worktime findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Worktime patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Worktime> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Worktime|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Worktime saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Worktime>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Worktime>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Worktime>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Worktime> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Worktime>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Worktime>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Worktime>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Worktime> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class WorktimesTable extends Table
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
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

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
            ->boolean('base')
            ->notEmptyString('base');

        $validator
            ->scalar('code')
            ->maxLength('code', 16)
            ->requirePresence('code', 'create')
            ->notEmptyString('code')
            ->add('code', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('name')
            ->maxLength('name', 32)
            ->requirePresence('name', 'create')
            ->notEmptyString('name')
            ->add('name', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('sort')
            ->maxLength('sort', 32)
            ->notEmptyString('sort');

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
        $rules->add($rules->isUnique(['name']), ['errorField' => 'name']);

        return $rules;
    }
}
