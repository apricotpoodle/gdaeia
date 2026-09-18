<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * FieldAuthorizations Model
 *
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\RolesTable> $Roles
 * @method \App\Model\Entity\FieldAuthorization newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\FieldAuthorization[] newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\FieldAuthorization get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\FieldAuthorization findOrCreate(\Cake\ORM\Query\SelectQuery<\App\Model\Entity\FieldAuthorization>|callable|array<string, mixed> $search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\FieldAuthorization patchEntity(\App\Model\Entity\FieldAuthorization $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\FieldAuthorization[] patchEntities(iterable<\App\Model\Entity\FieldAuthorization> $entities, array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\FieldAuthorization|false save(\App\Model\Entity\FieldAuthorization $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\FieldAuthorization saveOrFail(\App\Model\Entity\FieldAuthorization $entity, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\FieldAuthorization>|false saveMany(iterable<\App\Model\Entity\FieldAuthorization> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\FieldAuthorization> saveManyOrFail(iterable<\App\Model\Entity\FieldAuthorization> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\FieldAuthorization>|false deleteMany(iterable<\App\Model\Entity\FieldAuthorization> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\FieldAuthorization> deleteManyOrFail(iterable<\App\Model\Entity\FieldAuthorization> $entities, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}, \App\Model\Entity\FieldAuthorization>
 * @method bool delete(\App\Model\Entity\FieldAuthorization $entity, array<string, mixed> $options = [])
 * @method bool deleteOrFail(\App\Model\Entity\FieldAuthorization $entity, array<string, mixed> $options = [])
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
        $rules->add($rules->isUnique(['role_id', 'resource', 'field']), [
            'errorField' => 'role_id',
            'message' => __('This combination of role_id, resource and field already exists'),
        ]);
        $rules->add($rules->existsIn(['role_id'], 'Roles'), ['errorField' => 'role_id']);

        return $rules;
    }
}
