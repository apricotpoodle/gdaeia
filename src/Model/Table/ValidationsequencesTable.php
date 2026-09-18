<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Validationsequences Model
 *
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\DepartmentsTable> $Departments
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\RolesTable> $Roles
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\ApplicationvalidationstepsTable> $Applicationvalidationsteps
 * @method \App\Model\Entity\Validationsequence newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Validationsequence[] newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Validationsequence get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Validationsequence findOrCreate(\Cake\ORM\Query\SelectQuery<\App\Model\Entity\Validationsequence>|callable|array<string, mixed> $search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Validationsequence patchEntity(\App\Model\Entity\Validationsequence $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Validationsequence[] patchEntities(iterable<\App\Model\Entity\Validationsequence> $entities, array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Validationsequence|false save(\App\Model\Entity\Validationsequence $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Validationsequence saveOrFail(\App\Model\Entity\Validationsequence $entity, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Validationsequence>|false saveMany(iterable<\App\Model\Entity\Validationsequence> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Validationsequence> saveManyOrFail(iterable<\App\Model\Entity\Validationsequence> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Validationsequence>|false deleteMany(iterable<\App\Model\Entity\Validationsequence> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Validationsequence> deleteManyOrFail(iterable<\App\Model\Entity\Validationsequence> $entities, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}, \App\Model\Entity\Validationsequence>
 * @method bool delete(\App\Model\Entity\Validationsequence $entity, array<string, mixed> $options = [])
 * @method bool deleteOrFail(\App\Model\Entity\Validationsequence $entity, array<string, mixed> $options = [])
 */
class ValidationsequencesTable extends Table
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

        $this->setTable('validationsequences');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Departments', [
            'foreignKey' => 'department_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Roles', [
            'foreignKey' => 'role_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('Applicationvalidationsteps', [
            'foreignKey' => 'validationsequence_id',
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
            ->nonNegativeInteger('department_id')
            ->notEmptyString('department_id');

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->allowEmptyString('name');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->nonNegativeInteger('role_id')
            ->notEmptyString('role_id');

        $validator
            ->integer('sequence')
            ->greaterThanOrEqual('sequence', 1, __('Le numéro de séquence doit être supérieur ou égal à 1.'))
            ->notEmptyString('sequence');

        $validator
            ->nonNegativeInteger('reminder_delay_hours')
            ->greaterThan('reminder_delay_hours', 0, __('Le délai doit être un entier positif.'))
            ->allowEmptyString('reminder_delay_hours');

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
        $rules->add($rules->isUnique(['department_id', 'role_id']), [
            'errorField' => 'department_id',
            'message' => __('This combination of department_id and role_id already exists'),
        ]);
        $rules->add($rules->existsIn(['department_id'], 'Departments'), ['errorField' => 'department_id']);
        $rules->add($rules->existsIn(['role_id'], 'Roles'), ['errorField' => 'role_id']);

        return $rules;
    }

    /**
     * Limite une requête aux séquences actives d'un ensemble de départements.
     *
     * @param \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> $query Requête à compléter.
     * @param array<int> $departmentIds Départements dont la configuration est contrôlée.
     * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface>
     */
    public function findActiveForDepartments(SelectQuery $query, array $departmentIds): SelectQuery
    {
        return $query->where([
            'Validationsequences.department_id IN' => $departmentIds,
            'Validationsequences.deleted IS' => null,
        ]);
    }

    /**
     * Vérifie que chaque département possède les numéros continus de 1 à son maximum.
     * Plusieurs rôles peuvent partager un numéro et valident alors en parallèle.
     *
     * @param array<int> $departmentIds Départements dont la configuration est contrôlée.
     * @return bool Vrai lorsque chaque département possède une configuration vide ou une séquence continue.
     */
    public function hasContiguousSequencesForDepartments(array $departmentIds): bool
    {
        $sequencesByDepartment = array_fill_keys($departmentIds, []);
        $rows = $this->find('activeForDepartments', departmentIds: $departmentIds)
            ->select(['department_id', 'sequence'])
            ->all();
        foreach ($rows as $row) {
            $sequencesByDepartment[(int)$row->department_id][(int)$row->sequence] = true;
        }

        foreach ($sequencesByDepartment as $sequences) {
            if ($sequences === []) {
                continue;
            }
            $numbers = array_keys($sequences);
            sort($numbers, SORT_NUMERIC);
            if ($numbers !== range(1, max($numbers))) {
                return false;
            }
        }

        return true;
    }
}
