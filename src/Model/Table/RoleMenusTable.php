<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\RoleMenu;
use App\Model\Entity\User;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * RoleMenus Model
 *
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\RolesTable> $Roles
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\MenusTable> $Menus
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\DepartmentsTable> $Departments
 * @method \App\Model\Entity\RoleMenu newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\RoleMenu[] newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\RoleMenu get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\RoleMenu findOrCreate(\Cake\ORM\Query\SelectQuery<\App\Model\Entity\RoleMenu>|callable|array<string, mixed> $search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\RoleMenu patchEntity(\App\Model\Entity\RoleMenu $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\RoleMenu[] patchEntities(iterable<\App\Model\Entity\RoleMenu> $entities, array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\RoleMenu|false save(\App\Model\Entity\RoleMenu $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\RoleMenu saveOrFail(\App\Model\Entity\RoleMenu $entity, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\RoleMenu>|false saveMany(iterable<\App\Model\Entity\RoleMenu> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\RoleMenu> saveManyOrFail(iterable<\App\Model\Entity\RoleMenu> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\RoleMenu>|false deleteMany(iterable<\App\Model\Entity\RoleMenu> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\RoleMenu> deleteManyOrFail(iterable<\App\Model\Entity\RoleMenu> $entities, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}, \App\Model\Entity\RoleMenu>
 * @method bool delete(\App\Model\Entity\RoleMenu $entity, array<string, mixed> $options = [])
 * @method bool deleteOrFail(\App\Model\Entity\RoleMenu $entity, array<string, mixed> $options = [])
 */
class RoleMenusTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('role_menus');
        $this->setDisplayField(RoleMenu::FIELD_ID);
        $this->setPrimaryKey(RoleMenu::FIELD_ID);

        $this->addBehavior('Timestamp');

        $this->belongsTo('Roles', [
            'foreignKey' => RoleMenu::FIELD_ROLE_ID,
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Menus', [
            'foreignKey' => RoleMenu::FIELD_MENU_ID,
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Departments', [
            'foreignKey' => RoleMenu::FIELD_DEPARTMENT_ID,
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer(RoleMenu::FIELD_ROLE_ID)
            ->notEmptyString(RoleMenu::FIELD_ROLE_ID);

        $validator
            ->integer(RoleMenu::FIELD_MENU_ID)
            ->notEmptyString(RoleMenu::FIELD_MENU_ID);

        $validator
            ->nonNegativeInteger(RoleMenu::FIELD_DEPARTMENT_ID)
            ->allowEmptyString(RoleMenu::FIELD_DEPARTMENT_ID);

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(
            [RoleMenu::FIELD_ROLE_ID, RoleMenu::FIELD_MENU_ID, RoleMenu::FIELD_DEPARTMENT_ID],
            ['allowMultipleNulls' => true],
        ), [
            'errorField' => RoleMenu::FIELD_ROLE_ID,
            'message' => __('Cette option de menu est déjà associée à ce rôle et à ce département.'),
        ]);
        $rules->add($rules->existsIn([RoleMenu::FIELD_ROLE_ID], 'Roles'), [
            'errorField' => RoleMenu::FIELD_ROLE_ID,
        ]);
        $rules->add($rules->existsIn([RoleMenu::FIELD_MENU_ID], 'Menus'), [
            'errorField' => RoleMenu::FIELD_MENU_ID,
        ]);
        $rules->add($rules->existsIn([RoleMenu::FIELD_DEPARTMENT_ID], 'Departments'), [
            'errorField' => RoleMenu::FIELD_DEPARTMENT_ID,
        ]);

        return $rules;
    }

    /**
     * Sous-requête des identifiants de rôles associés à toutes les options de menu.
     * Les associations départementales sont incluses : elles rendent elles
     * aussi l'option accessible au rôle.
     *
     * @param \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> $query Requête à filtrer.
     * @param array<int> $menuIds Options de menu sélectionnées.
     * @param \App\Model\Entity\User $user Opérateur connecté.
     * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface>
     */
    public function findRoleIdsAssociatedWithMenus(SelectQuery $query, array $menuIds, User $user): SelectQuery
    {
        $visibleRoles = $this->Roles->find('roleAccessVisibleTo', user: $user)
            ->select(['Roles.id']);

        return $query->select([RoleMenu::FIELD_ROLE_ID])
            ->distinct([RoleMenu::FIELD_ROLE_ID])
            ->where([
                'RoleMenus.menu_id IN' => $menuIds,
                'RoleMenus.role_id IN' => $visibleRoles,
            ])
            ->groupBy(['RoleMenus.role_id'])
            ->having(['COUNT(DISTINCT RoleMenus.menu_id) =' => count($menuIds)]);
    }
}
