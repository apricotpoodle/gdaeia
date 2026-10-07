<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\FieldAuthorization;
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
        $this->setDisplayField(FieldAuthorization::FIELD_RESOURCE);
        $this->setPrimaryKey(FieldAuthorization::FIELD_ID);

        $this->addBehavior('Timestamp');

        $this->belongsTo('Roles', [
            'foreignKey' => FieldAuthorization::FIELD_ROLE_ID,
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
            ->nonNegativeInteger(FieldAuthorization::FIELD_ROLE_ID)
            ->notEmptyString(FieldAuthorization::FIELD_ROLE_ID);

        $validator
            ->scalar(FieldAuthorization::FIELD_RESOURCE)
            ->maxLength(FieldAuthorization::FIELD_RESOURCE, 50)
            ->requirePresence(FieldAuthorization::FIELD_RESOURCE, 'create')
            ->notEmptyString(FieldAuthorization::FIELD_RESOURCE);

        $validator
            ->scalar(FieldAuthorization::FIELD_FIELD)
            ->maxLength(FieldAuthorization::FIELD_FIELD, 50)
            ->requirePresence(FieldAuthorization::FIELD_FIELD, 'create')
            ->notEmptyString(FieldAuthorization::FIELD_FIELD);

        $validator
            ->scalar(FieldAuthorization::FIELD_ACCESS_LEVEL)
            ->maxLength(FieldAuthorization::FIELD_ACCESS_LEVEL, 20)
            ->notEmptyString(FieldAuthorization::FIELD_ACCESS_LEVEL)
            ->inList(
                FieldAuthorization::FIELD_ACCESS_LEVEL,
                ['EDIT', 'VIEW', 'NONE'],
                __('Le niveau d’accès est invalide.'),
            );

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
        $rules->add($rules->isUnique([
            FieldAuthorization::FIELD_ROLE_ID,
            FieldAuthorization::FIELD_RESOURCE,
            FieldAuthorization::FIELD_FIELD,
        ]), [
            'errorField' => FieldAuthorization::FIELD_ROLE_ID,
            'message' => __('Une autorisation existe déjà pour ce rôle, cette ressource et ce champ.'),
        ]);
        $rules->add($rules->existsIn([FieldAuthorization::FIELD_ROLE_ID], 'Roles'), [
            'errorField' => FieldAuthorization::FIELD_ROLE_ID,
        ]);

        return $rules;
    }
}
