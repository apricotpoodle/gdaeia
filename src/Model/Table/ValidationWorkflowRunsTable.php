<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/** Table des exécutions de validation. */
final class ValidationWorkflowRunsTable extends Table
{
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

    public function validationDefault(Validator $validator): Validator
    {
        return $validator->integer('applicationform_id')->notEmptyString('applicationform_id')
            ->integer('started_by_user_id')->notEmptyString('started_by_user_id')
            ->inList('state', ['en_attente', 'acceptee', 'refusee', 'annulee'])->notEmptyString('state');
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['applicationform_id']), ['errorField' => 'applicationform_id']);
        $rules->add($rules->existsIn(['applicationform_id'], 'Applicationforms'));
        $rules->add($rules->existsIn(['started_by_user_id'], 'StartedByUsers'));
        return $rules;
    }
}
