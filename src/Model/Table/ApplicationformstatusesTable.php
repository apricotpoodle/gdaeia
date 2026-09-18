<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Applicationformstatuses Model
 *
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\ApplicationformsTable> $Applicationforms
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\ValidationstatusesTable> $Validationstatuses
 * @method \App\Model\Entity\Applicationformstatus newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Applicationformstatus[] newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Applicationformstatus get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Applicationformstatus findOrCreate(\Cake\ORM\Query\SelectQuery<\App\Model\Entity\Applicationformstatus>|callable|array<string, mixed> $search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Applicationformstatus patchEntity(\App\Model\Entity\Applicationformstatus $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Applicationformstatus[] patchEntities(iterable<\App\Model\Entity\Applicationformstatus> $entities, array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Applicationformstatus|false save(\App\Model\Entity\Applicationformstatus $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Applicationformstatus saveOrFail(\App\Model\Entity\Applicationformstatus $entity, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Applicationformstatus>|false saveMany(iterable<\App\Model\Entity\Applicationformstatus> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Applicationformstatus> saveManyOrFail(iterable<\App\Model\Entity\Applicationformstatus> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Applicationformstatus>|false deleteMany(iterable<\App\Model\Entity\Applicationformstatus> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Applicationformstatus> deleteManyOrFail(iterable<\App\Model\Entity\Applicationformstatus> $entities, array<string, mixed> $options = [])
 * @extends \Cake\ORM\Table<array{}, \App\Model\Entity\Applicationformstatus>
 * @method bool delete(\App\Model\Entity\Applicationformstatus $entity, array<string, mixed> $options = [])
 * @method bool deleteOrFail(\App\Model\Entity\Applicationformstatus $entity, array<string, mixed> $options = [])
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
        $rules->add($rules->existsIn(['applicationform_id'], 'Applicationforms'), [
            'errorField' => 'applicationform_id',
        ]);
        $rules->add($rules->existsIn(['validationstatus_id'], 'Validationstatuses'), [
            'errorField' => 'validationstatus_id',
        ]);

        return $rules;
    }
}
