<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Applicationformstatuses Model
 *
 * @property \App\Model\Table\ApplicationformsTable&\Cake\ORM\Association\BelongsTo $Applicationforms
 * @property \App\Model\Table\ValidationstatusesTable&\Cake\ORM\Association\BelongsTo $Validationstatuses
 *
 * @method \App\Model\Entity\Applicationformstatus newEmptyEntity()
 * @method \App\Model\Entity\Applicationformstatus newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Applicationformstatus> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Applicationformstatus get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Applicationformstatus findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Applicationformstatus patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Applicationformstatus> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Applicationformstatus|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Applicationformstatus saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Applicationformstatus>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Applicationformstatus>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Applicationformstatus>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Applicationformstatus> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Applicationformstatus>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Applicationformstatus>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Applicationformstatus>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Applicationformstatus> deleteManyOrFail(iterable $entities, array $options = [])
 */
class ApplicationformstatusesTable extends Table
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

        $this->setTable('applicationformstatuses');

        $this->belongsTo('Applicationforms', [
            'foreignKey' => 'applicationform_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Validationstatuses', [
            'foreignKey' => 'validationstatus_id',
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
            ->nonNegativeInteger('applicationform_id')
            ->notEmptyString('applicationform_id');

        $validator
            ->integer('has_validations')
            ->notEmptyString('has_validations');

        $validator
            ->integer('validationstatus_id')
            ->notEmptyString('validationstatus_id');

        $validator
            ->decimal('valid_percentage')
            ->allowEmptyString('valid_percentage');

        $validator
            ->allowEmptyString('current_sequence');

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
        $rules->add($rules->existsIn(['validationstatus_id'], 'Validationstatuses'), ['errorField' => 'validationstatus_id']);

        return $rules;
    }
}
