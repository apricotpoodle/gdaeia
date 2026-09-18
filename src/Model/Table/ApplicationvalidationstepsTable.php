<?php
declare(strict_types=1);

namespace App\Model\Table;

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
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Applicationforms', [
            'foreignKey' => 'applicationform_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Roles', [
            'foreignKey' => 'role_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Validationstatuses', [
            'foreignKey' => 'validationstatus_id',
            'joinType' => 'LEFT',
        ]);
        $this->belongsTo('Validationsequences', [
            'foreignKey' => 'validationsequence_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('ValidationWorkflowRuns', [
            'foreignKey' => 'validation_workflow_run_id',
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
            ->nonNegativeInteger('applicationform_id')
            ->notEmptyString('applicationform_id');

        $validator
            ->nonNegativeInteger('role_id')
            ->notEmptyString('role_id');

        $validator
            ->nonNegativeInteger('validationstatus_id')
            ->allowEmptyString('validationstatus_id');

        $validator
            ->scalar('comment')
            ->maxLength('comment', 100)
            ->allowEmptyString('comment');

        $validator
            ->nonNegativeInteger('validationsequence_id')
            ->notEmptyString('validationsequence_id');

        $validator
            ->dateTime('deleted')
            ->allowEmptyDateTime('deleted');

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
        $rules->add($rules->existsIn(['applicationform_id'], 'Applicationforms'), [
            'errorField' => 'applicationform_id',
        ]);
        $rules->add($rules->existsIn(['role_id'], 'Roles'), ['errorField' => 'role_id']);
        $rules->add($rules->existsIn(['validationstatus_id'], 'Validationstatuses'), [
            'errorField' => 'validationstatus_id',
            'allowNullableNulls' => true,
        ]);
        $rules->add($rules->existsIn(['validationsequence_id'], 'Validationsequences'), [
            'errorField' => 'validationsequence_id',
        ]);

        return $rules;
    }
}
