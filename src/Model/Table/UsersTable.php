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
 * @property \App\Model\Table\RolesTable&\Cake\ORM\Association\BelongsTo $Roles
 * @property \App\Model\Table\ApplicationformsTable&\Cake\ORM\Association\HasMany $Applicationforms
 * @property \App\Model\Table\UrdsTable&\Cake\ORM\Association\HasMany $Urds
 * @property \App\Model\Table\UserDepartmentsTable&\Cake\ORM\Association\HasMany $UserDepartments
 * @property \App\Model\Table\ValidationsTable&\Cake\ORM\Association\HasMany $Validations
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
        $this->setDisplayField('email');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Roles', [
            'foreignKey' => 'role_id',
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
            ->scalar('username')
            ->maxLength('username', 255)
            ->allowEmptyString('username');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmptyString('email');

        $validator
            ->scalar('password')
            ->maxLength('password', 255)
            ->requirePresence('password', 'create')
            ->notEmptyString('password');

        $validator
            ->scalar('firstname')
            ->maxLength('firstname', 255)
            ->allowEmptyString('firstname');

        $validator
            ->scalar('lastname')
            ->maxLength('lastname', 255)
            ->allowEmptyString('lastname');

        $validator
            ->boolean('issuperuser')
            ->notEmptyString('issuperuser');

        $validator
            ->integer('role_id')
            ->notEmptyString('role_id');

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
        $rules->add($rules->isUnique(['username']), ['errorField' => 'username']);
        $rules->add($rules->isUnique(['email']), ['errorField' => 'email']);
        $rules->add($rules->existsIn(['role_id'], 'Roles'), ['errorField' => 'role_id']);

        return $rules;
    }

    /**
     * Custom finder : Restreint la liste des utilisateurs à ceux visibles par l'opérateur.
     *
     * @param \Cake\ORM\Query\SelectQuery $query
     * @param \App\Model\Entity\User $user
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findVisibleTo(SelectQuery $query, User $user): SelectQuery
    {
        if ($user->get('issuperuser')) {
            return $query;
        }

        $myDepartmentIds = $this->UserDepartments->find('departmentsOf', user: $user);

        return $query->innerJoinWith('UserDepartments', function ($q) use ($myDepartmentIds) {
            return $q->where(['UserDepartments.department_id IN' => $myDepartmentIds]);
        })->distinct(['Users.id']);
    }
}