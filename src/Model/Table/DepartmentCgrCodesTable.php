<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\DepartmentCgrCode;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/** Table des associations entre départements et codes CGR. */
class DepartmentCgrCodesTable extends Table
{
    /**
     * Configure la table des associations département/CGR.
     *
     * @param array<string, mixed> $config Configuration de la table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('department_cgr_codes');
        $this->setPrimaryKey([
            DepartmentCgrCode::FIELD_DEPARTMENT_ID,
            DepartmentCgrCode::FIELD_CGR_CODE_ID,
        ]);

        $this->belongsTo('Departments', [
            'foreignKey' => DepartmentCgrCode::FIELD_DEPARTMENT_ID,
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('CgrCodes', [
            'foreignKey' => DepartmentCgrCode::FIELD_CGR_CODE_ID,
            'joinType' => 'INNER',
        ]);
    }

    /**
     * @param \Cake\Validation\Validator $validator Validateur à configurer.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator->nonNegativeInteger(DepartmentCgrCode::FIELD_DEPARTMENT_ID)
            ->notEmptyString(DepartmentCgrCode::FIELD_DEPARTMENT_ID);
        $validator->nonNegativeInteger(DepartmentCgrCode::FIELD_CGR_CODE_ID)
            ->notEmptyString(DepartmentCgrCode::FIELD_CGR_CODE_ID);

        return $validator;
    }

    /**
     * @param \Cake\ORM\RulesChecker $rules Vérificateur de règles à configurer.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn([DepartmentCgrCode::FIELD_DEPARTMENT_ID], 'Departments'));
        $rules->add($rules->existsIn([DepartmentCgrCode::FIELD_CGR_CODE_ID], 'CgrCodes'));

        return $rules;
    }
}
