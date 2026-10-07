<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Urd;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Urds Model
 *
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\UsersTable> $Users
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\RolesTable> $Roles
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\DepartmentsTable> $Departments
 * @method \App\Model\Entity\Urd newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Urd[] newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Urd get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Urd findOrCreate(\Cake\ORM\Query\SelectQuery<\App\Model\Entity\Urd>|callable|array<string, mixed> $search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Urd patchEntity(\App\Model\Entity\Urd $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Urd[] patchEntities(iterable<\App\Model\Entity\Urd> $entities, array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Urd|false save(\App\Model\Entity\Urd $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Urd saveOrFail(\App\Model\Entity\Urd $entity, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Urd>|false saveMany(iterable<\App\Model\Entity\Urd> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Urd> saveManyOrFail(iterable<\App\Model\Entity\Urd> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Urd>|false deleteMany(iterable<\App\Model\Entity\Urd> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Urd> deleteManyOrFail(iterable<\App\Model\Entity\Urd> $entities, array<string, mixed> $options = [])
 * @extends \Cake\ORM\Table<array{}, \App\Model\Entity\Urd>
 * @method bool delete(\App\Model\Entity\Urd $entity, array<string, mixed> $options = [])
 * @method bool deleteOrFail(\App\Model\Entity\Urd $entity, array<string, mixed> $options = [])
 */
class UrdsTable extends Table
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

        $this->setTable('urds');

        $this->belongsTo('Users', [
            'foreignKey' => Urd::FIELD_USER_ID,
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Roles', [
            'foreignKey' => Urd::FIELD_ROLE_ID,
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Departments', [
            'foreignKey' => Urd::FIELD_DEPARTMENT_ID,
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
            ->integer(Urd::FIELD_USER_ID)
            ->notEmptyString(Urd::FIELD_USER_ID);

        $validator
            ->integer(Urd::FIELD_ROLE_ID)
            ->notEmptyString(Urd::FIELD_ROLE_ID);

        $validator
            ->integer(Urd::FIELD_DEPARTMENT_ID)
            ->notEmptyString(Urd::FIELD_DEPARTMENT_ID);

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
        $rules->add($rules->existsIn([Urd::FIELD_USER_ID], 'Users'), ['errorField' => Urd::FIELD_USER_ID]);
        $rules->add($rules->existsIn([Urd::FIELD_ROLE_ID], 'Roles'), ['errorField' => Urd::FIELD_ROLE_ID]);
        $rules->add($rules->existsIn([Urd::FIELD_DEPARTMENT_ID], 'Departments'), [
            'errorField' => Urd::FIELD_DEPARTMENT_ID,
        ]);

        return $rules;
    }
}
