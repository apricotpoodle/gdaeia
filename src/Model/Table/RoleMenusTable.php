<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * RoleMenus Model
 *
 * @property \App\Model\Table\RolesTable&\Cake\ORM\Association\BelongsTo $Roles
 * @property \App\Model\Table\MenusTable&\Cake\ORM\Association\BelongsTo $Menus
 * @property \App\Model\Table\DepartmentsTable&\Cake\ORM\Association\BelongsTo $Departments
 *
 * @method \App\Model\Entity\RoleMenu newEmptyEntity()
 * @method \App\Model\Entity\RoleMenu newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\RoleMenu> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RoleMenu get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\RoleMenu findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\RoleMenu patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\RoleMenu> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\RoleMenu|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\RoleMenu saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\RoleMenu>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\RoleMenu>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\RoleMenu>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\RoleMenu> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\RoleMenu>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\RoleMenu>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\RoleMenu>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\RoleMenu> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
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
        $rules->add($rules->isUnique(['role_id', 'menu_id', 'department_id'], ['allowMultipleNulls' => true]), ['errorField' => 'role_id', 'message' => __('This combination of role_id, menu_id and department_id already exists')]);
        $rules->add($rules->existsIn(['role_id'], 'Roles'), ['errorField' => 'role_id']);
        $rules->add($rules->existsIn(['menu_id'], 'Menus'), ['errorField' => 'menu_id']);
        $rules->add($rules->existsIn(['department_id'], 'Departments'), ['errorField' => 'department_id']);

        return $rules;
    }
}
