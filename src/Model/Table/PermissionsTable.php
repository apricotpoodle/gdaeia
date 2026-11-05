<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Permission;
use App\Model\Entity\PermissionScope;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/** Catalogue des permissions applicatives. */
class PermissionsTable extends AppTable
{
    /** Initialise le catalogue et ses associations. */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('permissions');
        $this->setPrimaryKey(Permission::FIELD_ID);
        $this->setDisplayField(Permission::FIELD_CODE);
        $this->hasMany('PermissionScopes', ['foreignKey' => PermissionScope::FIELD_PERMISSION_ID]);
        $this->hasMany('RolePermissions', ['foreignKey' => 'permission_id']);
    }

    /** Valide la nomenclature et le libellé de la permission. */
    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->scalar(Permission::FIELD_RESOURCE)
            ->maxLength(Permission::FIELD_RESOURCE, 64)
            ->notEmptyString(Permission::FIELD_RESOURCE)
            ->scalar(Permission::FIELD_ACTION)
            ->maxLength(Permission::FIELD_ACTION, 64)
            ->notEmptyString(Permission::FIELD_ACTION)
            ->scalar(Permission::FIELD_LABEL)
            ->maxLength(Permission::FIELD_LABEL, 255)
            ->notEmptyString(Permission::FIELD_LABEL);
    }

    /** Vérifie l’unicité du couple ressource/action. */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique([Permission::FIELD_RESOURCE, Permission::FIELD_ACTION]));

        return $rules;
    }
}
