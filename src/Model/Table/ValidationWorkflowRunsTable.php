<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Applicationvalidationstep;
use App\Model\Entity\ValidationWorkflowRun;
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
        $this->setPrimaryKey(ValidationWorkflowRun::FIELD_ID);
        $this->addBehavior('Timestamp');
        $this->belongsTo('Applicationforms', ['foreignKey' => ValidationWorkflowRun::FIELD_APPLICATIONFORM_ID]);
        $this->belongsTo('StartedByUsers', [
            'className' => 'Users',
            'foreignKey' => ValidationWorkflowRun::FIELD_STARTED_BY_USER_ID,
        ]);
        $this->hasMany('Applicationvalidationsteps', [
            'foreignKey' => Applicationvalidationstep::FIELD_VALIDATION_WORKFLOW_RUN_ID,
        ]);
    }

    /** @inheritDoc */
    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->integer(ValidationWorkflowRun::FIELD_APPLICATIONFORM_ID)
            ->notEmptyString(ValidationWorkflowRun::FIELD_APPLICATIONFORM_ID)
            ->integer(ValidationWorkflowRun::FIELD_STARTED_BY_USER_ID)
            ->notEmptyString(ValidationWorkflowRun::FIELD_STARTED_BY_USER_ID)
            ->inList(
                ValidationWorkflowRun::FIELD_STATE,
                ['en_attente', 'acceptee', 'refusee', 'annulee'],
            )
            ->notEmptyString(ValidationWorkflowRun::FIELD_STATE);
    }

    /** @inheritDoc */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique([ValidationWorkflowRun::FIELD_APPLICATIONFORM_ID]), [
            'errorField' => ValidationWorkflowRun::FIELD_APPLICATIONFORM_ID,
        ]);
        $rules->add($rules->existsIn([ValidationWorkflowRun::FIELD_APPLICATIONFORM_ID], 'Applicationforms'));
        $rules->add($rules->existsIn([ValidationWorkflowRun::FIELD_STARTED_BY_USER_ID], 'StartedByUsers'));

        return $rules;
    }
}
