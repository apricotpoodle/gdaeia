<?php

declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * UserDepartments Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\DepartmentsTable&\Cake\ORM\Association\BelongsTo $Departments
 *
 * @method \App\Model\Entity\UserDepartment newEmptyEntity()
 * @method \App\Model\Entity\UserDepartment newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\UserDepartment> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\UserDepartment get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\UserDepartment findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\UserDepartment patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\UserDepartment> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\UserDepartment|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\UserDepartment saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\UserDepartment>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\UserDepartment>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\UserDepartment>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\UserDepartment> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\UserDepartment>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\UserDepartment>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\UserDepartment>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\UserDepartment> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UserDepartmentsTable extends Table
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

        $this->setTable('user_departments');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Departments', [
            'foreignKey' => 'department_id',
            'joinType' => 'INNER',
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
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->integer('department_id')
            ->notEmptyString('department_id');

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
        $rules->add($rules->isUnique(['user_id', 'department_id']), ['errorField' => 'user_id', 'message' => __('This combination of user_id and department_id already exists')]);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);
        $rules->add($rules->existsIn(['department_id'], 'Departments'), ['errorField' => 'department_id']);

        return $rules;
    }


    /**
     * Custom finder : Récupère la requête des lignes de départements associées à un utilisateur donné.
     * Utilisation : ->find('departmentsOf', user: $userEntity)
     *
     * @param \Cake\ORM\Query\SelectQuery $query L'objet Query de l'ORM.
     * @param \App\Model\Entity\User $user L'entité de l'opérateur.
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findDepartmentsOf(SelectQuery $query, \App\Model\Entity\User $user): SelectQuery
    {
        return $query->select(['UserDepartments.department_id'])
            ->where(['UserDepartments.user_id' => $user->id]);
    }
}
