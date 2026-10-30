<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\DepartmentManager;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/** Table des associations entre départements et responsables. */
class DepartmentManagersTable extends Table
{
    /**
     * Configure la table des associations département/responsable.
     *
     * @param array<string, mixed> $config Configuration de la table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('department_managers');
        $this->setPrimaryKey([
            DepartmentManager::FIELD_DEPARTMENT_ID,
            DepartmentManager::FIELD_USER_ID,
        ]);

        $this->belongsTo('Departments', [
            'foreignKey' => DepartmentManager::FIELD_DEPARTMENT_ID,
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => DepartmentManager::FIELD_USER_ID,
            'joinType' => 'INNER',
        ]);
    }

    /**
     * @param \Cake\Validation\Validator $validator Validateur à configurer.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator->nonNegativeInteger(DepartmentManager::FIELD_DEPARTMENT_ID)
            ->notEmptyString(DepartmentManager::FIELD_DEPARTMENT_ID);
        $validator->nonNegativeInteger(DepartmentManager::FIELD_USER_ID)
            ->notEmptyString(DepartmentManager::FIELD_USER_ID);

        return $validator;
    }

    /**
     * @param \Cake\ORM\RulesChecker $rules Vérificateur de règles à configurer.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn([DepartmentManager::FIELD_DEPARTMENT_ID], 'Departments'));
        $rules->add($rules->existsIn([DepartmentManager::FIELD_USER_ID], 'Users'));

        return $rules;
    }
}
