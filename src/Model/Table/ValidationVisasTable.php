<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ValidationVisas Model
 *
 * @property \App\Model\Table\ApplicationformsTable&\Cake\ORM\Association\BelongsTo $Applicationforms
 * @property \App\Model\Table\RolesTable&\Cake\ORM\Association\BelongsTo $Roles
 *
 * @method \App\Model\Entity\ValidationVisa newEmptyEntity()
 * @method \App\Model\Entity\ValidationVisa newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ValidationVisa> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ValidationVisa get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ValidationVisa findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ValidationVisa patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ValidationVisa> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ValidationVisa|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ValidationVisa saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\ValidationVisa>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ValidationVisa>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ValidationVisa>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ValidationVisa> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ValidationVisa>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ValidationVisa>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ValidationVisa>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ValidationVisa> deleteManyOrFail(iterable $entities, array $options = [])
 */
class ValidationVisasTable extends Table
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

        $this->setTable('validation_visas');
        $this->setDisplayField('role_name');

        $this->belongsTo('Applicationforms', [
            'foreignKey' => 'applicationform_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Roles', [
            'foreignKey' => 'role_id',
            'joinType' => 'INNER',
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
            ->nonNegativeInteger('applicationform_id')
            ->notEmptyString('applicationform_id');

        $validator
            ->integer('sequence')
            ->notEmptyString('sequence');

        $validator
            ->nonNegativeInteger('role_id')
            ->notEmptyString('role_id');

        $validator
            ->scalar('op_name')
            ->maxLength('op_name', 511)
            ->allowEmptyString('op_name');

        $validator
            ->scalar('role_name')
            ->maxLength('role_name', 64)
            ->requirePresence('role_name', 'create')
            ->notEmptyString('role_name');

        $validator
            ->scalar('status_name')
            ->maxLength('status_name', 100)
            ->allowEmptyString('status_name');

        $validator
            ->dateTime('validated_at')
            ->allowEmptyDateTime('validated_at');

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
        $rules->add($rules->existsIn(['applicationform_id'], 'Applicationforms'), ['errorField' => 'applicationform_id']);
        $rules->add($rules->existsIn(['role_id'], 'Roles'), ['errorField' => 'role_id']);

        return $rules;
    }
}
