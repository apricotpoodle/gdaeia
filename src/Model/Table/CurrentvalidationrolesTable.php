<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Currentvalidationroles Model
 *
 * @property \App\Model\Table\ApplicationformsTable&\Cake\ORM\Association\BelongsTo $Applicationforms
 * @property \App\Model\Table\DepartmentsTable&\Cake\ORM\Association\BelongsTo $Departments
 * @property \App\Model\Table\ValidationstatusesTable&\Cake\ORM\Association\BelongsTo $Validationstatuses
 *
 * @method \App\Model\Entity\Currentvalidationrole newEmptyEntity()
 * @method \App\Model\Entity\Currentvalidationrole newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Currentvalidationrole> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Currentvalidationrole get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Currentvalidationrole findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Currentvalidationrole patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Currentvalidationrole> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Currentvalidationrole|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Currentvalidationrole saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Currentvalidationrole>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Currentvalidationrole>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Currentvalidationrole>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Currentvalidationrole> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Currentvalidationrole>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Currentvalidationrole>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Currentvalidationrole>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Currentvalidationrole> deleteManyOrFail(iterable $entities, array $options = [])
 */
class CurrentvalidationrolesTable extends Table
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

        $this->setTable('currentvalidationroles');

        $this->belongsTo('Applicationforms', [
            'foreignKey' => 'applicationform_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Departments', [
            'foreignKey' => 'department_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Validationstatuses', [
            'foreignKey' => 'validationstatus_id',
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
            ->nonNegativeInteger('applicationform_id')
            ->notEmptyString('applicationform_id');

        $validator
            ->integer('department_id')
            ->notEmptyString('department_id');

        $validator
            ->nonNegativeInteger('validator_role_id')
            ->requirePresence('validator_role_id', 'create')
            ->notEmptyString('validator_role_id');

        $validator
            ->integer('validation_sequence')
            ->notEmptyString('validation_sequence');

        $validator
            ->integer('validationstatus_id')
            ->allowEmptyString('validationstatus_id');

        $validator
            ->integer('en_cours')
            ->notEmptyString('en_cours');

        $validator
            ->integer('accepted')
            ->notEmptyString('accepted');

        $validator
            ->integer('rejected')
            ->notEmptyString('rejected');

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
        $rules->add($rules->existsIn(['applicationform_id'], 'Applicationforms'), ['errorField' => 'applicationform_id']);
        $rules->add($rules->existsIn(['department_id'], 'Departments'), ['errorField' => 'department_id']);
        $rules->add($rules->existsIn(['validationstatus_id'], 'Validationstatuses'), ['errorField' => 'validationstatus_id']);

        return $rules;
    }
}
