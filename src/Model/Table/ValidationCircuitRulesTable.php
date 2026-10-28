<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\ValidationCircuitRule;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/** Persistance et validation des fins de circuit locales. */
class ValidationCircuitRulesTable extends Table
{
    /** Configure les associations de la table. */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('validation_circuit_rules');
        $this->setPrimaryKey(ValidationCircuitRule::FIELD_ID);
        $this->addBehavior('Timestamp');
        $this->belongsTo('Departments', ['foreignKey' => 'department_id']);
        $this->belongsTo('Contracttypes', ['foreignKey' => 'contracttype_id']);
        $this->belongsTo('FinalRoles', ['className' => 'Roles', 'foreignKey' => 'final_role_id']);
    }

    /** Définit les contraintes de saisie. */
    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->nonNegativeInteger('department_id')->notEmptyString('department_id')
            ->nonNegativeInteger('contracttype_id')->notEmptyString('contracttype_id')
            ->nonNegativeInteger('final_role_id')->notEmptyString('final_role_id');
    }

    /** Vérifie les références et l'unicité du couple métier. */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['department_id', 'contracttype_id']), [
            'errorField' => 'contracttype_id',
            'message' => __('Une règle existe déjà pour ce département et ce type de contrat.'),
        ]);
        $rules->add($rules->existsIn(['department_id'], 'Departments'));
        $rules->add($rules->existsIn(['contracttype_id'], 'Contracttypes'));
        $rules->add($rules->existsIn(['final_role_id'], 'FinalRoles'));

        return $rules;
    }
}
