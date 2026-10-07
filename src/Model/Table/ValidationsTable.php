<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Validation;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Validations Model
 *
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\ApplicationformsTable> $Applicationforms
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\UsersTable> $Users
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\RolesTable> $Roles
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\ValidationstatusesTable> $Validationstatuses
 * @method \App\Model\Entity\Validation newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Validation[] newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Validation get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Validation findOrCreate(\Cake\ORM\Query\SelectQuery<\App\Model\Entity\Validation>|callable|array<string, mixed> $search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Validation patchEntity(\App\Model\Entity\Validation $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Validation[] patchEntities(iterable<\App\Model\Entity\Validation> $entities, array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Validation|false save(\App\Model\Entity\Validation $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Validation saveOrFail(\App\Model\Entity\Validation $entity, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Validation>|false saveMany(iterable<\App\Model\Entity\Validation> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Validation> saveManyOrFail(iterable<\App\Model\Entity\Validation> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Validation>|false deleteMany(iterable<\App\Model\Entity\Validation> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Validation> deleteManyOrFail(iterable<\App\Model\Entity\Validation> $entities, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}, \App\Model\Entity\Validation>
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\ApplicationvalidationstepsTable> $Applicationvalidationsteps
 * @method bool delete(\App\Model\Entity\Validation $entity, array<string, mixed> $options = [])
 * @method bool deleteOrFail(\App\Model\Entity\Validation $entity, array<string, mixed> $options = [])
 */
class ValidationsTable extends Table
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

        $this->setTable('validations');
        $this->setDisplayField(Validation::FIELD_ID);
        $this->setPrimaryKey(Validation::FIELD_ID);

        $this->addBehavior('Timestamp');

        $this->belongsTo('Applicationforms', [
            'foreignKey' => Validation::FIELD_APPLICATIONFORM_ID,
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => Validation::FIELD_USER_ID,
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Roles', [
            'foreignKey' => Validation::FIELD_ROLE_ID,
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Validationstatuses', [
            'foreignKey' => Validation::FIELD_VALIDATIONSTATUS_ID,
        ]);
        $this->belongsTo('Applicationvalidationsteps', [
            'foreignKey' => Validation::FIELD_APPLICATIONVALIDATIONSTEP_ID,
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
            ->integer(Validation::FIELD_APPLICATIONFORM_ID)
            ->notEmptyString(Validation::FIELD_APPLICATIONFORM_ID);

        $validator
            ->integer(Validation::FIELD_USER_ID)
            ->notEmptyString(Validation::FIELD_USER_ID);

        $validator
            ->integer(Validation::FIELD_ROLE_ID)
            ->notEmptyString(Validation::FIELD_ROLE_ID);

        $validator
            ->dateTime(Validation::FIELD_VALIDATED)
            ->allowEmptyDateTime(Validation::FIELD_VALIDATED);

        $validator
            ->integer(Validation::FIELD_VALIDATIONSTATUS_ID)
            ->allowEmptyString(Validation::FIELD_VALIDATIONSTATUS_ID);

        $validator
            ->scalar(Validation::FIELD_OBS)
            ->maxLength(Validation::FIELD_OBS, 255)
            ->allowEmptyString(Validation::FIELD_OBS);

        $validator
            ->dateTime(Validation::FIELD_DELETED)
            ->allowEmptyDateTime(Validation::FIELD_DELETED);

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
        $rules->add($rules->existsIn([Validation::FIELD_APPLICATIONFORM_ID], 'Applicationforms'), [
            'errorField' => Validation::FIELD_APPLICATIONFORM_ID,
        ]);
        $rules->add($rules->existsIn([Validation::FIELD_USER_ID], 'Users'), ['errorField' => Validation::FIELD_USER_ID]);
        $rules->add($rules->existsIn([Validation::FIELD_ROLE_ID], 'Roles'), ['errorField' => Validation::FIELD_ROLE_ID]);
        $rules->add($rules->existsIn([Validation::FIELD_VALIDATIONSTATUS_ID], 'Validationstatuses'), [
            'errorField' => Validation::FIELD_VALIDATIONSTATUS_ID,
        ]);

        return $rules;
    }
}
