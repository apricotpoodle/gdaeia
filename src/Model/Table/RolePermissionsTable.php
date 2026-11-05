<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\RolePermission;
use App\Service\Security\PermissionResolver;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/** Attributions de permissions et de périmètres aux rôles. */
class RolePermissionsTable extends AppTable
{
    /** Initialise les associations des attributions. */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('role_permissions');
        $this->setPrimaryKey(RolePermission::FIELD_ID);
        $this->belongsTo('Roles', ['foreignKey' => RolePermission::FIELD_ROLE_ID]);
        $this->belongsTo('Permissions', ['foreignKey' => RolePermission::FIELD_PERMISSION_ID]);
    }

    /** Valide le rôle, la permission et le périmètre. */
    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->integer(RolePermission::FIELD_ROLE_ID)->notEmptyString(RolePermission::FIELD_ROLE_ID)
            ->integer(RolePermission::FIELD_PERMISSION_ID)->notEmptyString(RolePermission::FIELD_PERMISSION_ID)
            ->inList(RolePermission::FIELD_SCOPE_TYPE, PermissionResolver::SCOPE_TYPES);
    }

    /** Vérifie l’unicité et les références des attributions. */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique([
            RolePermission::FIELD_ROLE_ID,
            RolePermission::FIELD_PERMISSION_ID,
            RolePermission::FIELD_SCOPE_TYPE,
        ]));
        $rules->add($rules->existsIn([RolePermission::FIELD_ROLE_ID], 'Roles'));
        $rules->add($rules->existsIn([RolePermission::FIELD_PERMISSION_ID], 'Permissions'));

        return $rules;
    }

    /**
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\RolePermission> $query Requête.
     * @return \Cake\ORM\Query\SelectQuery<\App\Model\Entity\RolePermission> Requête filtrée.
     */
    public function findForRolePermission(SelectQuery $query, int $roleId, string $code): SelectQuery
    {
        return $query
            ->contain(['Permissions'])
            ->where([
                'RolePermissions.role_id' => $roleId,
                'Permissions.code' => $code,
            ]);
    }
}
