<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/** Référentiel des libellés et descriptions de champs métier. */
/**
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}, \App\Model\Entity\FieldDefinition>
 * @method \App\Model\Entity\FieldDefinition newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\FieldDefinition[] newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\FieldDefinition get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\FieldDefinition findOrCreate(\Cake\ORM\Query\SelectQuery<\App\Model\Entity\FieldDefinition>|callable|array<string, mixed> $search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\FieldDefinition patchEntity(\App\Model\Entity\FieldDefinition $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\FieldDefinition[] patchEntities(iterable<\App\Model\Entity\FieldDefinition> $entities, array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\FieldDefinition|false save(\App\Model\Entity\FieldDefinition $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\FieldDefinition saveOrFail(\App\Model\Entity\FieldDefinition $entity, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\FieldDefinition>|false saveMany(iterable<\App\Model\Entity\FieldDefinition> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\FieldDefinition> saveManyOrFail(iterable<\App\Model\Entity\FieldDefinition> $entities, array<string, mixed> $options = [])
 * @method bool delete(\App\Model\Entity\FieldDefinition $entity, array<string, mixed> $options = [])
 * @method bool deleteOrFail(\App\Model\Entity\FieldDefinition $entity, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\FieldDefinition>|false deleteMany(iterable<\App\Model\Entity\FieldDefinition> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\FieldDefinition> deleteManyOrFail(iterable<\App\Model\Entity\FieldDefinition> $entities, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
final class FieldDefinitionsTable extends Table
{
    /** @inheritDoc */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('field_definitions');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
    }

    /** @inheritDoc */
    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->scalar('resource')->maxLength('resource', 80)->notEmptyString('resource')
            ->scalar('field')->maxLength('field', 80)->notEmptyString('field')
            ->scalar('label')->maxLength('label', 160)->notEmptyString('label')
            ->allowEmptyString('description')
            ->boolean('active')->requirePresence('active', 'create')
            ->nonNegativeInteger('position')->requirePresence('position', 'create');
    }
}
