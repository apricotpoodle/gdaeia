<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Role;
use App\Model\Entity\User;
use Cake\I18n\DateTime;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * Roles Model
 *
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\ApplicationvalidationstepsTable> $Applicationvalidationsteps
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\FieldAuthorizationsTable> $FieldAuthorizations
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\RoleMenusTable> $RoleMenus
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\UrdsTable> $Urds
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\UsersTable> $Users
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\ValidationVisasTable> $ValidationVisas
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\ValidationsTable> $Validations
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\ValidationsequencesTable> $Validationsequences
 * @method \App\Model\Entity\Role newEmptyEntity()
 * @method \App\Model\Entity\Role newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\App\Model\Entity\Role> newEntities(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Role get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Role findOrCreate($search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Role patchEntity(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\App\Model\Entity\Role> patchEntities(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Role|false save(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Role saveOrFail(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Role>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Role>|false saveMany(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Role>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Role> saveManyOrFail(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Role>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Role>|false deleteMany(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\App\Model\Entity\Role>|\Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Role> deleteManyOrFail(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RolesTable extends AppTable
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

        $this->setTable('roles');
        $this->setDisplayField(Role::FIELD_NAME);
        $this->setPrimaryKey(Role::FIELD_ID);

        $this->hasMany('Applicationvalidationsteps', [
            'foreignKey' => 'role_id',
        ]);
        $this->hasMany('FieldAuthorizations', [
            'foreignKey' => 'role_id',
        ]);
        $this->hasMany('RoleMenus', [
            'foreignKey' => 'role_id',
        ]);
        $this->hasMany('Urds', [
            'foreignKey' => 'role_id',
        ]);
        $this->hasMany('Users', [
            'foreignKey' => 'role_id',
        ]);
        $this->hasMany('ValidationVisas', [
            'foreignKey' => 'role_id',
        ]);
        $this->hasMany('Validations', [
            'foreignKey' => 'role_id',
        ]);
        $this->hasMany('Validationsequences', [
            'foreignKey' => 'role_id',
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
            ->boolean(Role::FIELD_BASE)
            ->notEmptyString(Role::FIELD_BASE);

        $validator
            ->scalar(Role::FIELD_CODE)
            ->maxLength(Role::FIELD_CODE, 16)
            ->requirePresence(Role::FIELD_CODE, 'create', __('Ce champ est obligatoire.'))
            ->notEmptyString(Role::FIELD_CODE, __('Ce champ est obligatoire.'))
            /** @link validateUnique() */
            ->add(Role::FIELD_CODE, 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar(Role::FIELD_NAME)
            ->maxLength(Role::FIELD_NAME, 64)
            ->requirePresence(Role::FIELD_NAME, 'create', __('Ce champ est obligatoire.'))
            ->notEmptyString(Role::FIELD_NAME, __('Ce champ est obligatoire.'))
            /** @link validateUnique() */
            ->add(Role::FIELD_NAME, 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar(Role::FIELD_SORT)
            ->maxLength(Role::FIELD_SORT, 64)
            ->requirePresence(Role::FIELD_SORT, 'create', __('Ce champ est obligatoire.'))
            ->notEmptyString(Role::FIELD_SORT, __('Ce champ est obligatoire.'));

        $validator
            ->dateTime(Role::FIELD_DELETED)
            ->allowEmptyDateTime(Role::FIELD_DELETED);

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
        $rules->add($rules->isUnique([Role::FIELD_CODE]), ['errorField' => Role::FIELD_CODE]);
        $rules->add($rules->isUnique([Role::FIELD_NAME]), ['errorField' => Role::FIELD_NAME]);

        return $rules;
    }

    /**
     * Limite les rôles administrables depuis l'écran d'accès aux menus.
     *
     * @param \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> $query Requête à filtrer.
     * @param \App\Model\Entity\User $user Opérateur connecté.
     * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface>
     */
    public function findRoleAccessVisibleTo(SelectQuery $query, User $user): SelectQuery
    {
        $query = $this->findVisibleTo($query, $user);
        if (!$user->get(User::FIELD_ISSUPERUSER) && $user->get(User::FIELD_ROLE_ID) !== User::ROLE_ADMIN) {
            $query->where(['1 = 0']);
        }

        return $query;
    }

    /**
     * Désactive un rôle sans casser les relations historiques qui le référencent.
     *
     * @param \App\Model\Entity\Role $role Rôle à désactiver.
     * @return bool Vrai si la désactivation est enregistrée.
     */
    public function softDelete(Role $role): bool
    {
        $role->set(Role::FIELD_DELETED, DateTime::now());

        return (bool)$this->save($role);
    }
}
