<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Menu;
use App\Model\Entity\User;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * Menus Model
 *
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\MenusTable> $ParentMenus
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\MenusTable> $ChildMenus
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\RoleMenusTable> $RoleMenus
 * @method \App\Model\Entity\Menu newEmptyEntity()
 * @method \App\Model\Entity\Menu newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\App\Model\Entity\Menu> newEntities(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Menu get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Menu findOrCreate($search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Menu patchEntity(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\App\Model\Entity\Menu> patchEntities(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Menu|false save(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Menu saveOrFail(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Menu>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Menu>|false saveMany(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Menu>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Menu> saveManyOrFail(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Menu>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Menu>|false deleteMany(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Menu>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Menu> deleteManyOrFail(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TreeBehavior
 */
class MenusTable extends AppTable
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

        $this->setTable('menus');
        $this->setDisplayField(Menu::FIELD_NAME);
        $this->setPrimaryKey(Menu::FIELD_ID);

        $this->addBehavior(
            'Tree',
            [
                Menu::FIELD_LEVEL => Menu::FIELD_LEVEL, // Default to null, i.e. no level saving
            ],
        );

        $this->belongsTo('ParentMenus', [
            'className' => 'Menus',
            'foreignKey' => Menu::FIELD_PARENT_ID,
        ]);
        $this->hasMany('ChildMenus', [
            'className' => 'Menus',
            'foreignKey' => Menu::FIELD_PARENT_ID,
        ]);
        $this->hasMany('RoleMenus', [
            'foreignKey' => 'menu_id',
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
            ->integer(Menu::FIELD_PARENT_ID)
            ->allowEmptyString(Menu::FIELD_PARENT_ID);

        $validator
            ->integer(Menu::FIELD_LEVEL)
            ->allowEmptyString(Menu::FIELD_LEVEL);

        $validator
            ->scalar(Menu::FIELD_NAME)
            ->maxLength(Menu::FIELD_NAME, 255)
            ->allowEmptyString(Menu::FIELD_NAME);

        $validator
            ->scalar(Menu::FIELD_URL)
            ->maxLength(Menu::FIELD_URL, 255)
            ->allowEmptyString(Menu::FIELD_URL);

        $validator
            ->boolean(Menu::FIELD_ACTIVE)
            ->allowEmptyString(Menu::FIELD_ACTIVE);

        $validator
            ->boolean(Menu::FIELD_DISABLED)
            ->allowEmptyString(Menu::FIELD_DISABLED);

        $validator
            ->boolean(Menu::FIELD_DIVIDOR_BEFORE)
            ->allowEmptyString(Menu::FIELD_DIVIDOR_BEFORE);

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
        $rules->add($rules->existsIn([Menu::FIELD_PARENT_ID], 'ParentMenus'), ['errorField' => Menu::FIELD_PARENT_ID]);

        return $rules;
    }

    /**
     * Limite les options administrables dans l'écran d'accès aux rôles.
     * Le périmètre est volontairement identique à MenuPolicy : seuls les
     * super-administrateurs et le rôle administrateur administrent les menus.
     *
     * @param \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> $query Requête à filtrer.
     * @param \App\Model\Entity\User $user Opérateur connecté.
     * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface>
     */
    public function findRoleAccessVisibleTo(SelectQuery $query, User $user): SelectQuery
    {
        $query->where(['Menus.active' => true]);
        if (!$user->get(User::FIELD_ISSUPERUSER) && $user->get(User::FIELD_ROLE_ID) !== User::ROLE_ADMIN) {
            $query->where(['1 = 0']);
        }

        return $query;
    }
}
