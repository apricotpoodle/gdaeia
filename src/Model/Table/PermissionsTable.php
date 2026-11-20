<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Menu;
use App\Model\Entity\Permission;
use App\Model\Entity\PermissionScope;
use App\Model\Entity\RolePermission;
use Cake\Collection\CollectionInterface;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Query\SelectQuery;
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
        $this->hasMany('Menus', ['foreignKey' => Menu::FIELD_REQUIRED_PERMISSION_ID]);
    }

    /**
     * Charge toutes les permissions avec leurs périmètres et les destinations liées.
     *
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\Permission> $query Requête du catalogue.
     * @param int|null $roleId Rôle dont les attributions sont chargées, si choisi.
     * @return \Cake\ORM\Query\SelectQuery<\App\Model\Entity\Permission>
     */
    public function findCatalog(SelectQuery $query, ?int $roleId = null): SelectQuery
    {
        $query->contain(['PermissionScopes', 'Menus' => static function (SelectQuery $menus): SelectQuery {
            return $menus->select([
                Menu::FIELD_ID,
                Menu::FIELD_NAME,
                Menu::FIELD_URL,
                Menu::FIELD_REQUIRED_PERMISSION_ID,
            ]);
        }]);

        if ($roleId !== null) {
            $query->contain([
                'RolePermissions' => static fn(SelectQuery $assignments): SelectQuery => $assignments->where([
                    'RolePermissions.' . RolePermission::FIELD_ROLE_ID => $roleId,
                ]),
            ]);
        }

        // Attendre le rattachement ORM pour retirer la clé étrangère des résultats en tableau.
        return $query->formatResults(static fn(CollectionInterface $results): CollectionInterface => $results->map(
            static function (EntityInterface|array $permission): EntityInterface|array {
                if (is_array($permission)) {
                    foreach (array_keys($permission['menus'] ?? []) as $index) {
                        unset($permission['menus'][$index][Menu::FIELD_REQUIRED_PERMISSION_ID]);
                    }
                } else {
                    foreach ($permission->get('menus') ?? [] as $menu) {
                        $menu->setHidden([Menu::FIELD_REQUIRED_PERMISSION_ID], merge: true);
                    }
                }

                return $permission;
            },
        ));
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
