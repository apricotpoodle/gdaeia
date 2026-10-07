<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Currentvalidationrole;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Currentvalidationroles Model
 *
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\ApplicationformsTable> $Applicationforms
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\DepartmentsTable> $Departments
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\ValidationstatusesTable> $Validationstatuses
 * @method \App\Model\Entity\Currentvalidationrole newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Currentvalidationrole[] newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Currentvalidationrole get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Currentvalidationrole findOrCreate(\Cake\ORM\Query\SelectQuery<\App\Model\Entity\Currentvalidationrole>|callable|array<string, mixed> $search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Currentvalidationrole patchEntity(\App\Model\Entity\Currentvalidationrole $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Currentvalidationrole[] patchEntities(iterable<\App\Model\Entity\Currentvalidationrole> $entities, array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Currentvalidationrole|false save(\App\Model\Entity\Currentvalidationrole $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Currentvalidationrole saveOrFail(\App\Model\Entity\Currentvalidationrole $entity, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Currentvalidationrole>|false saveMany(iterable<\App\Model\Entity\Currentvalidationrole> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Currentvalidationrole> saveManyOrFail(iterable<\App\Model\Entity\Currentvalidationrole> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Currentvalidationrole>|false deleteMany(iterable<\App\Model\Entity\Currentvalidationrole> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Currentvalidationrole> deleteManyOrFail(iterable<\App\Model\Entity\Currentvalidationrole> $entities, array<string, mixed> $options = [])
 * @extends \Cake\ORM\Table<array{}, \App\Model\Entity\Currentvalidationrole>
 * @method bool delete(\App\Model\Entity\Currentvalidationrole $entity, array<string, mixed> $options = [])
 * @method bool deleteOrFail(\App\Model\Entity\Currentvalidationrole $entity, array<string, mixed> $options = [])
 */
class CurrentvalidationrolesTable extends Table
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

        $this->setTable('currentvalidationroles');

        $this->belongsTo('Applicationforms', [
            'foreignKey' => Currentvalidationrole::FIELD_APPLICATIONFORM_ID,
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Departments', [
            'foreignKey' => Currentvalidationrole::FIELD_DEPARTMENT_ID,
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Validationstatuses', [
            'foreignKey' => Currentvalidationrole::FIELD_VALIDATIONSTATUS_ID,
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
            ->nonNegativeInteger(Currentvalidationrole::FIELD_APPLICATIONFORM_ID)
            ->notEmptyString(Currentvalidationrole::FIELD_APPLICATIONFORM_ID);

        $validator
            ->integer(Currentvalidationrole::FIELD_DEPARTMENT_ID)
            ->notEmptyString(Currentvalidationrole::FIELD_DEPARTMENT_ID);

        $validator
            ->nonNegativeInteger(Currentvalidationrole::FIELD_VALIDATOR_ROLE_ID)
            ->requirePresence(Currentvalidationrole::FIELD_VALIDATOR_ROLE_ID, 'create')
            ->notEmptyString(Currentvalidationrole::FIELD_VALIDATOR_ROLE_ID);

        $validator
            ->integer(Currentvalidationrole::FIELD_VALIDATION_SEQUENCE)
            ->notEmptyString(Currentvalidationrole::FIELD_VALIDATION_SEQUENCE);

        $validator
            ->integer(Currentvalidationrole::FIELD_VALIDATIONSTATUS_ID)
            ->allowEmptyString(Currentvalidationrole::FIELD_VALIDATIONSTATUS_ID);

        $validator
            ->integer(Currentvalidationrole::FIELD_EN_COURS)
            ->notEmptyString(Currentvalidationrole::FIELD_EN_COURS);

        $validator
            ->integer(Currentvalidationrole::FIELD_ACCEPTED)
            ->notEmptyString(Currentvalidationrole::FIELD_ACCEPTED);

        $validator
            ->integer(Currentvalidationrole::FIELD_REJECTED)
            ->notEmptyString(Currentvalidationrole::FIELD_REJECTED);

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
        $rules->add($rules->existsIn([Currentvalidationrole::FIELD_APPLICATIONFORM_ID], 'Applicationforms'), [
            'errorField' => Currentvalidationrole::FIELD_APPLICATIONFORM_ID,
        ]);
        $rules->add($rules->existsIn([Currentvalidationrole::FIELD_DEPARTMENT_ID], 'Departments'), [
            'errorField' => Currentvalidationrole::FIELD_DEPARTMENT_ID,
        ]);
        $rules->add($rules->existsIn([Currentvalidationrole::FIELD_VALIDATIONSTATUS_ID], 'Validationstatuses'), [
            'errorField' => Currentvalidationrole::FIELD_VALIDATIONSTATUS_ID,
        ]);

        return $rules;
    }
}
