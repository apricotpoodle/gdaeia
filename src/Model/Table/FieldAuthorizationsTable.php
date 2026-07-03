<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * FieldAuthorizations Model
 *
 * @property \App\Model\Table\RolesTable&\Cake\ORM\Association\BelongsTo $Roles
 *
 * @method \App\Model\Entity\FieldAuthorization newEmptyEntity()
 * @method \App\Model\Entity\FieldAuthorization newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\FieldAuthorization> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\FieldAuthorization get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\FieldAuthorization findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\FieldAuthorization patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\FieldAuthorization> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\FieldAuthorization|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\FieldAuthorization saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\FieldAuthorization>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\FieldAuthorization>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\FieldAuthorization>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\FieldAuthorization> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\FieldAuthorization>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\FieldAuthorization>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\FieldAuthorization>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\FieldAuthorization> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class FieldAuthorizationsTable extends Table
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

        $this->setTable('field_authorizations');
        $this->setDisplayField('resource');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

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
            ->nonNegativeInteger('role_id')
            ->notEmptyString('role_id');

        $validator
            ->scalar('resource')
            ->maxLength('resource', 50)
            ->requirePresence('resource', 'create')
            ->notEmptyString('resource');

        $validator
            ->scalar('field')
            ->maxLength('field', 50)
            ->requirePresence('field', 'create')
            ->notEmptyString('field');

        $validator
            ->scalar('access_level')
            ->maxLength('access_level', 20)
            ->notEmptyString('access_level');

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
        $rules->add($rules->isUnique(['role_id', 'resource', 'field']), ['errorField' => 'role_id', 'message' => __('This combination of role_id, resource and field already exists')]);
        $rules->add($rules->existsIn(['role_id'], 'Roles'), ['errorField' => 'role_id']);

        return $rules;
    }
}
