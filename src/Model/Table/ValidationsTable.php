<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Validations Model
 *
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\ApplicationformsTable> $Applicationforms
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\UsersTable> $Users
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\RolesTable> $Roles
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\ValidationstatusesTable> $Validationstatuses
 * @method \App\Model\Entity\Validation newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Validation[] newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Validation get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Validation findOrCreate(\Cake\ORM\Query\SelectQuery<\App\Model\Entity\Validation>|callable|array<string, mixed> $search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Validation patchEntity(\App\Model\Entity\Validation $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Validation[] patchEntities(iterable<\App\Model\Entity\Validation> $entities, array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Validation|false save(\App\Model\Entity\Validation $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Validation saveOrFail(\App\Model\Entity\Validation $entity, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Validation>|false saveMany(iterable<\App\Model\Entity\Validation> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Validation> saveManyOrFail(iterable<\App\Model\Entity\Validation> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Validation>|false deleteMany(iterable<\App\Model\Entity\Validation> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Validation> deleteManyOrFail(iterable<\App\Model\Entity\Validation> $entities, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}, \App\Model\Entity\Validation>
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\ApplicationvalidationstepsTable> $Applicationvalidationsteps
 * @method bool delete(\App\Model\Entity\Validation $entity, array<string, mixed> $options = [])
 * @method bool deleteOrFail(\App\Model\Entity\Validation $entity, array<string, mixed> $options = [])
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
        $this->belongsTo('Applicationvalidationsteps', [
            'foreignKey' => 'applicationvalidationstep_id',
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
        $rules->add($rules->existsIn(['applicationform_id'], 'Applicationforms'), [
            'errorField' => 'applicationform_id',
        ]);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);
        $rules->add($rules->existsIn(['role_id'], 'Roles'), ['errorField' => 'role_id']);
        $rules->add($rules->existsIn(['validationstatus_id'], 'Validationstatuses'), [
            'errorField' => 'validationstatus_id',
        ]);

        return $rules;
    }
}
