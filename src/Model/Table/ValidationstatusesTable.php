<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Validationstatuses Model
 *
 * @property \App\Model\Table\ApplicationformstatusesTable&\Cake\ORM\Association\HasMany $Applicationformstatuses
 * @property \App\Model\Table\ApplicationvalidationstepsTable&\Cake\ORM\Association\HasMany $Applicationvalidationsteps
 * @property \App\Model\Table\CurrentvalidationrolesTable&\Cake\ORM\Association\HasMany $Currentvalidationroles
 * @property \App\Model\Table\ValidationsTable&\Cake\ORM\Association\HasMany $Validations
 *
 * @method \App\Model\Entity\Validationstatus newEmptyEntity()
 * @method \App\Model\Entity\Validationstatus newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Validationstatus> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Validationstatus get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Validationstatus findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Validationstatus patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Validationstatus> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Validationstatus|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Validationstatus saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Validationstatus>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Validationstatus>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Validationstatus>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Validationstatus> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Validationstatus>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Validationstatus>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Validationstatus>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Validationstatus> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ValidationstatusesTable extends Table
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

        $this->setTable('validationstatuses');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Applicationformstatuses', [
            'foreignKey' => 'validationstatus_id',
        ]);
        $this->hasMany('Applicationvalidationsteps', [
            'foreignKey' => 'validationstatus_id',
        ]);
        $this->hasMany('Currentvalidationroles', [
            'foreignKey' => 'validationstatus_id',
        ]);
        $this->hasMany('Validations', [
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
            ->scalar('code')
            ->maxLength('code', 100)
            ->requirePresence('code', 'create')
            ->notEmptyString('code');

        $validator
            ->scalar('name')
            ->maxLength('name', 100)
            ->allowEmptyString('name');

        $validator
            ->dateTime('deleted')
            ->allowEmptyDateTime('deleted');

        return $validator;
    }
}
