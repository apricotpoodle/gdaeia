<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Validationsequence;
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
        $this->setDisplayField(Validationsequence::FIELD_NAME);
        $this->setPrimaryKey(Validationsequence::FIELD_ID);

        $this->addBehavior('Timestamp');

        $this->belongsTo('Departments', [
            'foreignKey' => Validationsequence::FIELD_DEPARTMENT_ID,
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Roles', [
            'foreignKey' => Validationsequence::FIELD_ROLE_ID,
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
            ->nonNegativeInteger(Validationsequence::FIELD_DEPARTMENT_ID)
            ->notEmptyString(Validationsequence::FIELD_DEPARTMENT_ID);

        $validator
            ->scalar(Validationsequence::FIELD_NAME)
            ->maxLength(Validationsequence::FIELD_NAME, 255)
            ->allowEmptyString(Validationsequence::FIELD_NAME);

        $validator
            ->scalar(Validationsequence::FIELD_DESCRIPTION)
            ->allowEmptyString(Validationsequence::FIELD_DESCRIPTION);

        $validator
            ->nonNegativeInteger(Validationsequence::FIELD_ROLE_ID)
            ->notEmptyString(Validationsequence::FIELD_ROLE_ID);

        $validator
            ->integer(Validationsequence::FIELD_SEQUENCE)
            ->greaterThanOrEqual(Validationsequence::FIELD_SEQUENCE, 1, __('Le numéro de séquence doit être supérieur ou égal à 1.'))
            ->notEmptyString(Validationsequence::FIELD_SEQUENCE);

        $validator
            ->nonNegativeInteger(Validationsequence::FIELD_REMINDER_DELAY_HOURS)
            ->greaterThan(Validationsequence::FIELD_REMINDER_DELAY_HOURS, 0, __('Le délai doit être un entier positif.'))
            ->allowEmptyString(Validationsequence::FIELD_REMINDER_DELAY_HOURS);

        $validator
            ->dateTime(Validationsequence::FIELD_DELETED)
            ->allowEmptyDateTime(Validationsequence::FIELD_DELETED);

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
        $rules->add($rules->isUnique([Validationsequence::FIELD_DEPARTMENT_ID, Validationsequence::FIELD_ROLE_ID]), [
            'errorField' => Validationsequence::FIELD_DEPARTMENT_ID,
            'message' => __('Ce rôle possède déjà une séquence de validation pour ce département.'),
        ]);
        $rules->add($rules->existsIn([Validationsequence::FIELD_DEPARTMENT_ID], 'Departments'), ['errorField' => Validationsequence::FIELD_DEPARTMENT_ID]);
        $rules->add($rules->existsIn([Validationsequence::FIELD_ROLE_ID], 'Roles'), ['errorField' => Validationsequence::FIELD_ROLE_ID]);

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
            ->select([Validationsequence::FIELD_DEPARTMENT_ID, Validationsequence::FIELD_SEQUENCE])
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
