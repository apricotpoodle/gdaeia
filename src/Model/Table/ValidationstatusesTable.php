<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Validationstatuses Model
 *
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\ApplicationformstatusesTable> $Applicationformstatuses
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\ApplicationvalidationstepsTable> $Applicationvalidationsteps
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\CurrentvalidationrolesTable> $Currentvalidationroles
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\ValidationsTable> $Validations
 * @method \App\Model\Entity\Validationstatus newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Validationstatus[] newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Validationstatus get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Validationstatus findOrCreate(\Cake\ORM\Query\SelectQuery<\App\Model\Entity\Validationstatus>|callable|array<string, mixed> $search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Validationstatus patchEntity(\App\Model\Entity\Validationstatus $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Validationstatus[] patchEntities(iterable<\App\Model\Entity\Validationstatus> $entities, array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Validationstatus|false save(\App\Model\Entity\Validationstatus $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Validationstatus saveOrFail(\App\Model\Entity\Validationstatus $entity, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Validationstatus>|false saveMany(iterable<\App\Model\Entity\Validationstatus> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Validationstatus> saveManyOrFail(iterable<\App\Model\Entity\Validationstatus> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Validationstatus>|false deleteMany(iterable<\App\Model\Entity\Validationstatus> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Validationstatus> deleteManyOrFail(iterable<\App\Model\Entity\Validationstatus> $entities, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}, \App\Model\Entity\Validationstatus>
 * @method bool delete(\App\Model\Entity\Validationstatus $entity, array<string, mixed> $options = [])
 * @method bool deleteOrFail(\App\Model\Entity\Validationstatus $entity, array<string, mixed> $options = [])
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
