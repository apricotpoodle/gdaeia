<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Roles Model
 *
 * @property \App\Model\Table\ApplicationvalidationstepsTable&\Cake\ORM\Association\HasMany $Applicationvalidationsteps
 * @property \App\Model\Table\FieldAuthorizationsTable&\Cake\ORM\Association\HasMany $FieldAuthorizations
 * @property \App\Model\Table\RoleMenusTable&\Cake\ORM\Association\HasMany $RoleMenus
 * @property \App\Model\Table\UrdsTable&\Cake\ORM\Association\HasMany $Urds
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\HasMany $Users
 * @property \App\Model\Table\ValidationVisasTable&\Cake\ORM\Association\HasMany $ValidationVisas
 * @property \App\Model\Table\ValidationsTable&\Cake\ORM\Association\HasMany $Validations
 * @property \App\Model\Table\ValidationsequencesTable&\Cake\ORM\Association\HasMany $Validationsequences
 *
 * @method \App\Model\Entity\Role newEmptyEntity()
 * @method \App\Model\Entity\Role newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Role> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Role get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Role findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Role patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Role> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Role|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Role saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Role>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Role>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Role>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Role> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Role>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Role>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Role>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Role> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RolesTable extends Table
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
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

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
            ->boolean('base')
            ->notEmptyString('base');

        $validator
            ->scalar('code')
            ->maxLength('code', 16)
            ->requirePresence('code', 'create')
            ->notEmptyString('code')
            ->add('code', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('name')
            ->maxLength('name', 64)
            ->requirePresence('name', 'create')
            ->notEmptyString('name')
            ->add('name', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('sort')
            ->maxLength('sort', 64)
            ->notEmptyString('sort');

        $validator
            ->dateTime('deleted')
            ->allowEmptyDateTime('deleted');

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
        $rules->add($rules->isUnique(['code']), ['errorField' => 'code']);
        $rules->add($rules->isUnique(['name']), ['errorField' => 'name']);

        return $rules;
    }
}
