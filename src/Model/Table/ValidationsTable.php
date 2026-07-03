<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Validations Model
 *
 * @property \App\Model\Table\ApplicationformsTable&\Cake\ORM\Association\BelongsTo $Applicationforms
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\RolesTable&\Cake\ORM\Association\BelongsTo $Roles
 * @property \App\Model\Table\ValidationstatusesTable&\Cake\ORM\Association\BelongsTo $Validationstatuses
 *
 * @method \App\Model\Entity\Validation newEmptyEntity()
 * @method \App\Model\Entity\Validation newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Validation> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Validation get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Validation findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Validation patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Validation> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Validation|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Validation saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Validation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Validation>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Validation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Validation> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Validation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Validation>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Validation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Validation> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ValidationsTable extends Table
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

        $this->setTable('validations');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Applicationforms', [
            'foreignKey' => 'applicationform_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Roles', [
            'foreignKey' => 'role_id',
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
            ->integer('applicationform_id')
            ->notEmptyString('applicationform_id');

        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->integer('role_id')
            ->notEmptyString('role_id');

        $validator
            ->dateTime('validated')
            ->allowEmptyDateTime('validated');

        $validator
            ->integer('validationstatus_id')
            ->allowEmptyString('validationstatus_id');

        $validator
            ->scalar('obs')
            ->maxLength('obs', 255)
            ->allowEmptyString('obs');

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
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);
        $rules->add($rules->existsIn(['role_id'], 'Roles'), ['errorField' => 'role_id']);
        $rules->add($rules->existsIn(['validationstatus_id'], 'Validationstatuses'), ['errorField' => 'validationstatus_id']);

        return $rules;
    }
}
