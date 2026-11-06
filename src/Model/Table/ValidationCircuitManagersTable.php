<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/** Persiste les désignations locales, distinctes des rôles finaux de validation. */
class ValidationCircuitManagersTable extends Table
{
    /** Configure les références des habilitations. */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('validation_circuit_managers');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Departments');
        $this->belongsTo('Roles');
    }

    /** Vérifie les identifiants obligatoires. */
    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->integer('department_id')->greaterThan('department_id', 0)->requirePresence('department_id', 'create')
            ->notEmptyString('department_id')
            ->integer('role_id')->greaterThan('role_id', 0)
            ->requirePresence('role_id', 'create')->notEmptyString('role_id');
    }

    /** Garantit l'unicité et les références en complément des contraintes MySQL. */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['department_id', 'role_id']));
        $rules->add($rules->existsIn('department_id', 'Departments'));
        $rules->add($rules->existsIn('role_id', 'Roles'));

        return $rules;
    }
}
