<?php
declare(strict_types=1);

namespace App\Model\Table;

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
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Roles', [
            'foreignKey' => 'role_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Menus', [
            'foreignKey' => 'menu_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Departments', [
            'foreignKey' => 'department_id',
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
            ->integer('role_id')
            ->notEmptyString('role_id');

        $validator
            ->integer('menu_id')
            ->notEmptyString('menu_id');

        $validator
            ->nonNegativeInteger('department_id')
            ->allowEmptyString('department_id');

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
            ['role_id', 'menu_id', 'department_id'],
            ['allowMultipleNulls' => true],
        ), [
            'errorField' => 'role_id',
            'message' => __('This combination of role_id, menu_id and department_id already exists'),
        ]);
        $rules->add($rules->existsIn(['role_id'], 'Roles'), ['errorField' => 'role_id']);
        $rules->add($rules->existsIn(['menu_id'], 'Menus'), ['errorField' => 'menu_id']);
        $rules->add($rules->existsIn(['department_id'], 'Departments'), ['errorField' => 'department_id']);

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

        return $query->select(['role_id'])
            ->distinct(['role_id'])
            ->where([
                'RoleMenus.menu_id IN' => $menuIds,
                'RoleMenus.role_id IN' => $visibleRoles,
            ])
            ->groupBy(['RoleMenus.role_id'])
            ->having(['COUNT(DISTINCT RoleMenus.menu_id) =' => count($menuIds)]);
    }
}
