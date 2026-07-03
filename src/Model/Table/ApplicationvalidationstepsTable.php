<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Applicationvalidationsteps Model
 *
 * @property \App\Model\Table\ApplicationformsTable&\Cake\ORM\Association\BelongsTo $Applicationforms
 * @property \App\Model\Table\RolesTable&\Cake\ORM\Association\BelongsTo $Roles
 * @property \App\Model\Table\ValidationstatusesTable&\Cake\ORM\Association\BelongsTo $Validationstatuses
 * @property \App\Model\Table\ValidationsequencesTable&\Cake\ORM\Association\BelongsTo $Validationsequences
 *
 * @method \App\Model\Entity\Applicationvalidationstep newEmptyEntity()
 * @method \App\Model\Entity\Applicationvalidationstep newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Applicationvalidationstep> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Applicationvalidationstep get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Applicationvalidationstep findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Applicationvalidationstep patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Applicationvalidationstep> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Applicationvalidationstep|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Applicationvalidationstep saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Applicationvalidationstep>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Applicationvalidationstep>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Applicationvalidationstep>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Applicationvalidationstep> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Applicationvalidationstep>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Applicationvalidationstep>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Applicationvalidationstep>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Applicationvalidationstep> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ApplicationvalidationstepsTable extends Table
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

        $this->setTable('applicationvalidationsteps');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Applicationforms', [
            'foreignKey' => 'applicationform_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Roles', [
            'foreignKey' => 'role_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Validationstatuses', [
            'foreignKey' => 'validationstatus_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Validationsequences', [
            'foreignKey' => 'validationsequence_id',
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
            ->nonNegativeInteger('role_id')
            ->notEmptyString('role_id');

        $validator
            ->nonNegativeInteger('validationstatus_id')
            ->notEmptyString('validationstatus_id');

        $validator
            ->scalar('comment')
            ->maxLength('comment', 100)
            ->allowEmptyString('comment');

        $validator
            ->nonNegativeInteger('validationsequence_id')
            ->notEmptyString('validationsequence_id');

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
        $rules->add($rules->existsIn(['applicationform_id'], 'Applicationforms'), ['errorField' => 'applicationform_id']);
        $rules->add($rules->existsIn(['role_id'], 'Roles'), ['errorField' => 'role_id']);
        $rules->add($rules->existsIn(['validationstatus_id'], 'Validationstatuses'), ['errorField' => 'validationstatus_id']);
        $rules->add($rules->existsIn(['validationsequence_id'], 'Validationsequences'), ['errorField' => 'validationsequence_id']);

        return $rules;
    }
}
