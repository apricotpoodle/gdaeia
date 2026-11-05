<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\PermissionScope;
use App\Service\Security\PermissionResolver;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/** Périmètres déclarés comme admissibles pour les permissions. */
class PermissionScopesTable extends AppTable
{
    /** Initialise la table des périmètres. */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('permission_scopes');
        $this->setPrimaryKey(PermissionScope::FIELD_ID);
        $this->belongsTo('Permissions', ['foreignKey' => PermissionScope::FIELD_PERMISSION_ID]);
    }

    /** Valide le type et la permission du périmètre. */
    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->integer(PermissionScope::FIELD_PERMISSION_ID)->notEmptyString(PermissionScope::FIELD_PERMISSION_ID)
            ->inList(PermissionScope::FIELD_SCOPE_TYPE, PermissionResolver::SCOPE_TYPES);
    }

    /** Vérifie l’unicité et l’existence de la permission. */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique([PermissionScope::FIELD_PERMISSION_ID, PermissionScope::FIELD_SCOPE_TYPE]));
        $rules->add($rules->existsIn([PermissionScope::FIELD_PERMISSION_ID], 'Permissions'));

        return $rules;
    }
}
