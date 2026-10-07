<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Applicationvalidationstep;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Applicationvalidationsteps Model
 *
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\ApplicationformsTable> $Applicationforms
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\RolesTable> $Roles
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\ValidationstatusesTable> $Validationstatuses
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\ValidationsequencesTable> $Validationsequences
 * @method \App\Model\Entity\Applicationvalidationstep newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Applicationvalidationstep[] newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Applicationvalidationstep get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Applicationvalidationstep findOrCreate(\Cake\ORM\Query\SelectQuery<\App\Model\Entity\Applicationvalidationstep>|callable|array<string, mixed> $search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Applicationvalidationstep patchEntity(\App\Model\Entity\Applicationvalidationstep $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Applicationvalidationstep[] patchEntities(iterable<\App\Model\Entity\Applicationvalidationstep> $entities, array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Applicationvalidationstep|false save(\App\Model\Entity\Applicationvalidationstep $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Applicationvalidationstep saveOrFail(\App\Model\Entity\Applicationvalidationstep $entity, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Applicationvalidationstep>|false saveMany(iterable<\App\Model\Entity\Applicationvalidationstep> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Applicationvalidationstep> saveManyOrFail(iterable<\App\Model\Entity\Applicationvalidationstep> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Applicationvalidationstep>|false deleteMany(iterable<\App\Model\Entity\Applicationvalidationstep> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Applicationvalidationstep> deleteManyOrFail(iterable<\App\Model\Entity\Applicationvalidationstep> $entities, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}, \App\Model\Entity\Applicationvalidationstep>
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\ValidationWorkflowRunsTable> $ValidationWorkflowRuns
 * @property \Cake\ORM\Association\HasOne<\App\Model\Table\ValidationsTable> $Validations
 * @method bool delete(\App\Model\Entity\Applicationvalidationstep $entity, array<string, mixed> $options = [])
 * @method bool deleteOrFail(\App\Model\Entity\Applicationvalidationstep $entity, array<string, mixed> $options = [])
 */
class ApplicationvalidationstepsTable extends Table
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

        $this->setTable('applicationvalidationsteps');
        $this->setDisplayField(Applicationvalidationstep::FIELD_ID);
        $this->setPrimaryKey(Applicationvalidationstep::FIELD_ID);

        $this->addBehavior('Timestamp');

        $this->belongsTo('Applicationforms', [
            'foreignKey' => Applicationvalidationstep::FIELD_APPLICATIONFORM_ID,
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Roles', [
            'foreignKey' => Applicationvalidationstep::FIELD_ROLE_ID,
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Validationstatuses', [
            'foreignKey' => Applicationvalidationstep::FIELD_VALIDATIONSTATUS_ID,
            'joinType' => 'LEFT',
        ]);
        $this->belongsTo('Validationsequences', [
            'foreignKey' => Applicationvalidationstep::FIELD_VALIDATIONSEQUENCE_ID,
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('ValidationWorkflowRuns', [
            'foreignKey' => Applicationvalidationstep::FIELD_VALIDATION_WORKFLOW_RUN_ID,
        ]);
        $this->hasOne('Validations', [
            'foreignKey' => 'applicationvalidationstep_id',
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
            ->nonNegativeInteger(Applicationvalidationstep::FIELD_APPLICATIONFORM_ID)
            ->notEmptyString(Applicationvalidationstep::FIELD_APPLICATIONFORM_ID);

        $validator
            ->nonNegativeInteger(Applicationvalidationstep::FIELD_ROLE_ID)
            ->notEmptyString(Applicationvalidationstep::FIELD_ROLE_ID);

        $validator
            ->nonNegativeInteger(Applicationvalidationstep::FIELD_VALIDATIONSTATUS_ID)
            ->allowEmptyString(Applicationvalidationstep::FIELD_VALIDATIONSTATUS_ID);

        $validator
            ->nonNegativeInteger(Applicationvalidationstep::FIELD_VALIDATIONSEQUENCE_ID)
            ->notEmptyString(Applicationvalidationstep::FIELD_VALIDATIONSEQUENCE_ID);

        $validator
            ->dateTime(Applicationvalidationstep::FIELD_DELETED)
            ->allowEmptyDateTime(Applicationvalidationstep::FIELD_DELETED);

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
        $rules->add($rules->existsIn([Applicationvalidationstep::FIELD_APPLICATIONFORM_ID], 'Applicationforms'), [
            'errorField' => Applicationvalidationstep::FIELD_APPLICATIONFORM_ID,
        ]);
        $rules->add($rules->existsIn([Applicationvalidationstep::FIELD_ROLE_ID], 'Roles'), [
            'errorField' => Applicationvalidationstep::FIELD_ROLE_ID,
        ]);
        $rules->add($rules->existsIn([Applicationvalidationstep::FIELD_VALIDATIONSTATUS_ID], 'Validationstatuses'), [
            'errorField' => Applicationvalidationstep::FIELD_VALIDATIONSTATUS_ID,
            'allowNullableNulls' => true,
        ]);
        $rules->add($rules->existsIn([Applicationvalidationstep::FIELD_VALIDATIONSEQUENCE_ID], 'Validationsequences'), [
            'errorField' => Applicationvalidationstep::FIELD_VALIDATIONSEQUENCE_ID,
        ]);

        return $rules;
    }
}
