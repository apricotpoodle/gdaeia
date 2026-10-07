<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\ValidationVisa;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ValidationVisas Model
 *
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\ApplicationformsTable> $Applicationforms
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\RolesTable> $Roles
 * @method \App\Model\Entity\ValidationVisa newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\ValidationVisa[] newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\ValidationVisa get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ValidationVisa findOrCreate(\Cake\ORM\Query\SelectQuery<\App\Model\Entity\ValidationVisa>|callable|array<string, mixed> $search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\ValidationVisa patchEntity(\App\Model\Entity\ValidationVisa $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\ValidationVisa[] patchEntities(iterable<\App\Model\Entity\ValidationVisa> $entities, array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\ValidationVisa|false save(\App\Model\Entity\ValidationVisa $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\ValidationVisa saveOrFail(\App\Model\Entity\ValidationVisa $entity, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\ValidationVisa>|false saveMany(iterable<\App\Model\Entity\ValidationVisa> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\ValidationVisa> saveManyOrFail(iterable<\App\Model\Entity\ValidationVisa> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\ValidationVisa>|false deleteMany(iterable<\App\Model\Entity\ValidationVisa> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\ValidationVisa> deleteManyOrFail(iterable<\App\Model\Entity\ValidationVisa> $entities, array<string, mixed> $options = [])
 * @extends \Cake\ORM\Table<array{}, \App\Model\Entity\ValidationVisa>
 * @method bool delete(\App\Model\Entity\ValidationVisa $entity, array<string, mixed> $options = [])
 * @method bool deleteOrFail(\App\Model\Entity\ValidationVisa $entity, array<string, mixed> $options = [])
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
        $this->setDisplayField(ValidationVisa::FIELD_ROLE_NAME);

        $this->belongsTo('Applicationforms', [
            'foreignKey' => ValidationVisa::FIELD_APPLICATIONFORM_ID,
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Roles', [
            'foreignKey' => ValidationVisa::FIELD_ROLE_ID,
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
            ->nonNegativeInteger(ValidationVisa::FIELD_APPLICATIONFORM_ID)
            ->notEmptyString(ValidationVisa::FIELD_APPLICATIONFORM_ID);

        $validator
            ->integer(ValidationVisa::FIELD_SEQUENCE)
            ->notEmptyString(ValidationVisa::FIELD_SEQUENCE);

        $validator
            ->nonNegativeInteger(ValidationVisa::FIELD_ROLE_ID)
            ->notEmptyString(ValidationVisa::FIELD_ROLE_ID);

        $validator
            ->scalar(ValidationVisa::FIELD_OP_NAME)
            ->maxLength(ValidationVisa::FIELD_OP_NAME, 511)
            ->allowEmptyString(ValidationVisa::FIELD_OP_NAME);

        $validator
            ->scalar(ValidationVisa::FIELD_ROLE_NAME)
            ->maxLength(ValidationVisa::FIELD_ROLE_NAME, 64)
            ->requirePresence(ValidationVisa::FIELD_ROLE_NAME, 'create')
            ->notEmptyString(ValidationVisa::FIELD_ROLE_NAME);

        $validator
            ->scalar(ValidationVisa::FIELD_STATUS_NAME)
            ->maxLength(ValidationVisa::FIELD_STATUS_NAME, 100)
            ->allowEmptyString(ValidationVisa::FIELD_STATUS_NAME);

        $validator
            ->dateTime(ValidationVisa::FIELD_VALIDATED_AT)
            ->allowEmptyDateTime(ValidationVisa::FIELD_VALIDATED_AT);

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
        $rules->add($rules->existsIn([ValidationVisa::FIELD_APPLICATIONFORM_ID], 'Applicationforms'), [
            'errorField' => ValidationVisa::FIELD_APPLICATIONFORM_ID,
        ]);
        $rules->add($rules->existsIn([ValidationVisa::FIELD_ROLE_ID], 'Roles'), [
            'errorField' => ValidationVisa::FIELD_ROLE_ID,
        ]);

        return $rules;
    }
}
