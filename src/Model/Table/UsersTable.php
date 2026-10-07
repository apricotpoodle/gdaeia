<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\User;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Users Model
 *
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\RolesTable> $Roles
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\ApplicationformsTable> $Applicationforms
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\UrdsTable> $Urds
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\UserDepartmentsTable> $UserDepartments
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\ValidationsTable> $Validations
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}, \App\Model\Entity\User>
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\User>|false saveMany(iterable<\App\Model\Entity\User> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\User> saveManyOrFail(iterable<\App\Model\Entity\User> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\User>|false deleteMany(iterable<\App\Model\Entity\User> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\User> deleteManyOrFail(iterable<\App\Model\Entity\User> $entities, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @method \App\Model\Entity\User newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\User[] newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\User get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\User findOrCreate(\Cake\ORM\Query\SelectQuery<\App\Model\Entity\User>|callable|array<string, mixed> $search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\User patchEntity(\App\Model\Entity\User $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\User[] patchEntities(iterable<\App\Model\Entity\User> $entities, array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\User|false save(\App\Model\Entity\User $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\User saveOrFail(\App\Model\Entity\User $entity, array<string, mixed> $options = [])
 * @method bool delete(\App\Model\Entity\User $entity, array<string, mixed> $options = [])
 * @method bool deleteOrFail(\App\Model\Entity\User $entity, array<string, mixed> $options = [])
 */
class UsersTable extends Table
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

        $this->setTable('users');
        $this->setDisplayField(User::FIELD_EMAIL);
        $this->setPrimaryKey(User::FIELD_ID);

        $this->addBehavior('Timestamp');

        $this->belongsTo('Roles', [
            'foreignKey' => User::FIELD_ROLE_ID,
            'joinType' => 'INNER',
        ]);
        $this->hasMany('Applicationforms', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('Urds', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('UserDepartments', [
            'foreignKey' => 'user_id',
            'cascadeCallbacks' => true,
            'dependent' => true,
            'saveStrategy' => 'replace',
        ]);
        $this->hasMany('Validations', [
            'foreignKey' => 'user_id',
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
            ->scalar(User::FIELD_USERNAME)
            ->maxLength(User::FIELD_USERNAME, 255)
            ->allowEmptyString(User::FIELD_USERNAME);

        $validator
            ->email(User::FIELD_EMAIL)
            ->requirePresence(User::FIELD_EMAIL, 'create')
            ->notEmptyString(User::FIELD_EMAIL);

        $validator
            ->scalar(User::FIELD_PASSWORD)
            ->maxLength(User::FIELD_PASSWORD, 255)
            ->requirePresence(User::FIELD_PASSWORD, 'create')
            ->notEmptyString(User::FIELD_PASSWORD);

        $validator
            ->scalar(User::FIELD_FIRSTNAME)
            ->maxLength(User::FIELD_FIRSTNAME, 255)
            ->allowEmptyString(User::FIELD_FIRSTNAME);

        $validator
            ->scalar(User::FIELD_LASTNAME)
            ->maxLength(User::FIELD_LASTNAME, 255)
            ->allowEmptyString(User::FIELD_LASTNAME);

        $validator
            ->boolean(User::FIELD_ISSUPERUSER)
            ->notEmptyString(User::FIELD_ISSUPERUSER);

        $validator
            ->integer(User::FIELD_ROLE_ID)
            ->notEmptyString(User::FIELD_ROLE_ID);

        return $validator;
    }

    /**
     * Returns a rules checker object.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique([User::FIELD_USERNAME]), ['errorField' => User::FIELD_USERNAME]);
        $rules->add($rules->isUnique([User::FIELD_EMAIL]), ['errorField' => User::FIELD_EMAIL]);
        $rules->add($rules->existsIn([User::FIELD_ROLE_ID], 'Roles'), ['errorField' => User::FIELD_ROLE_ID]);

        return $rules;
    }

    /**
     * Custom finder : Restreint la liste des utilisateurs à ceux visibles par l'opérateur.
     *
     * @param \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> $query
     * @param \App\Model\Entity\User $user
     * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface>
     */
    public function findVisibleTo(SelectQuery $query, User $user): SelectQuery
    {
        if ($user->get(User::FIELD_ISSUPERUSER)) {
            return $query;
        }

        $myDepartmentIds = $this->UserDepartments->find('departmentsOf', user: $user);

        return $query->innerJoinWith('UserDepartments', function ($q) use ($myDepartmentIds) {
            return $q->where(['UserDepartments.department_id IN' => $myDepartmentIds]);
        })->distinct(['Users.id']);
    }

    /**
     * Restreint aux utilisateurs associés à tous les départements sélectionnés.
     *
     * @param \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> $query Requête à filtrer.
     * @param list<int> $departmentIds Départements explicitement sélectionnés.
     * @param \App\Model\Entity\User $user Opérateur connecté.
     * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface>
     */
    public function findAssociatedWithDepartments(SelectQuery $query, array $departmentIds, User $user): SelectQuery
    {
        return $this->findVisibleTo($query, $user)
            ->innerJoinWith('UserDepartments', function (
                SelectQuery $associationQuery,
            ) use ($departmentIds): SelectQuery {
                return $associationQuery->where(['UserDepartments.department_id IN' => $departmentIds]);
            })
            ->groupBy(['Users.id'])
            ->having(['COUNT(DISTINCT UserDepartments.department_id) =' => count($departmentIds)]);
    }

    /**
     * Restreint aux utilisateurs qui ne sont pas associés à tous les départements sélectionnés.
     *
     * @param \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> $query Requête à filtrer.
     * @param list<int> $departmentIds Départements explicitement sélectionnés.
     * @param \App\Model\Entity\User $user Opérateur connecté.
     * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface>
     */
    public function findNotAssociatedWithDepartments(SelectQuery $query, array $departmentIds, User $user): SelectQuery
    {
        $associatedUserIds = $this->UserDepartments->find(
            'userIdsAssociatedWithDepartments',
            departmentIds: $departmentIds,
        );

        return $this->findVisibleTo($query, $user)->where(['Users.id NOT IN' => $associatedUserIds]);
    }
}
