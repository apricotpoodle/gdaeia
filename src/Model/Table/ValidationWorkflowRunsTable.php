<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/** Table des exécutions de validation. */
/**
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}, \App\Model\Entity\ValidationWorkflowRun>
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\ApplicationformsTable> $Applicationforms
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\UsersTable> $StartedByUsers
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\ApplicationvalidationstepsTable> $Applicationvalidationsteps
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\ValidationWorkflowRun>|false saveMany(iterable<\App\Model\Entity\ValidationWorkflowRun> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\ValidationWorkflowRun> saveManyOrFail(iterable<\App\Model\Entity\ValidationWorkflowRun> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\ValidationWorkflowRun>|false deleteMany(iterable<\App\Model\Entity\ValidationWorkflowRun> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\ValidationWorkflowRun> deleteManyOrFail(iterable<\App\Model\Entity\ValidationWorkflowRun> $entities, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @method \App\Model\Entity\ValidationWorkflowRun newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\ValidationWorkflowRun[] newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\ValidationWorkflowRun get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ValidationWorkflowRun findOrCreate(\Cake\ORM\Query\SelectQuery<\App\Model\Entity\ValidationWorkflowRun>|callable|array<string, mixed> $search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\ValidationWorkflowRun patchEntity(\App\Model\Entity\ValidationWorkflowRun $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\ValidationWorkflowRun[] patchEntities(iterable<\App\Model\Entity\ValidationWorkflowRun> $entities, array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\ValidationWorkflowRun|false save(\App\Model\Entity\ValidationWorkflowRun $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\ValidationWorkflowRun saveOrFail(\App\Model\Entity\ValidationWorkflowRun $entity, array<string, mixed> $options = [])
 * @method bool delete(\App\Model\Entity\ValidationWorkflowRun $entity, array<string, mixed> $options = [])
 * @method bool deleteOrFail(\App\Model\Entity\ValidationWorkflowRun $entity, array<string, mixed> $options = [])
 */
final class ValidationWorkflowRunsTable extends Table
{
    /** @inheritDoc */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('validation_workflow_runs');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Applicationforms', ['foreignKey' => 'applicationform_id']);
        $this->belongsTo('StartedByUsers', ['className' => 'Users', 'foreignKey' => 'started_by_user_id']);
        $this->hasMany('Applicationvalidationsteps', ['foreignKey' => 'validation_workflow_run_id']);
    }

    /** @inheritDoc */
    public function validationDefault(Validator $validator): Validator
    {
        return $validator->integer('applicationform_id')->notEmptyString('applicationform_id')
            ->integer('started_by_user_id')->notEmptyString('started_by_user_id')
            ->inList('state', ['en_attente', 'acceptee', 'refusee', 'annulee'])->notEmptyString('state');
    }

    /** @inheritDoc */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['applicationform_id']), ['errorField' => 'applicationform_id']);
        $rules->add($rules->existsIn(['applicationform_id'], 'Applicationforms'));
        $rules->add($rules->existsIn(['started_by_user_id'], 'StartedByUsers'));

        return $rules;
    }
}
