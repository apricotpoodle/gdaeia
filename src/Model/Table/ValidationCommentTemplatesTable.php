<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}, \App\Model\Entity\ValidationCommentTemplate>
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @method \App\Model\Entity\ValidationCommentTemplate newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\ValidationCommentTemplate[] newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\ValidationCommentTemplate get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ValidationCommentTemplate findOrCreate(\Cake\ORM\Query\SelectQuery<\App\Model\Entity\ValidationCommentTemplate>|callable|array<string, mixed> $search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\ValidationCommentTemplate patchEntity(\App\Model\Entity\ValidationCommentTemplate $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\ValidationCommentTemplate[] patchEntities(iterable<\App\Model\Entity\ValidationCommentTemplate> $entities, array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\ValidationCommentTemplate|false save(\App\Model\Entity\ValidationCommentTemplate $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\ValidationCommentTemplate saveOrFail(\App\Model\Entity\ValidationCommentTemplate $entity, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\ValidationCommentTemplate>|false saveMany(iterable<\App\Model\Entity\ValidationCommentTemplate> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\ValidationCommentTemplate> saveManyOrFail(iterable<\App\Model\Entity\ValidationCommentTemplate> $entities, array<string, mixed> $options = [])
 * @method bool delete(\App\Model\Entity\ValidationCommentTemplate $entity, array<string, mixed> $options = [])
 * @method bool deleteOrFail(\App\Model\Entity\ValidationCommentTemplate $entity, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\ValidationCommentTemplate>|false deleteMany(iterable<\App\Model\Entity\ValidationCommentTemplate> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\ValidationCommentTemplate> deleteManyOrFail(iterable<\App\Model\Entity\ValidationCommentTemplate> $entities, array<string, mixed> $options = [])
 */
final class ValidationCommentTemplatesTable extends Table
{
    /** @inheritDoc */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('validation_comment_templates');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
    }

    /** @inheritDoc */
    public function validationDefault(Validator $validator): Validator
    {
        return $validator->inList('decision', ['accepter', 'refuser'])->notEmptyString('decision')
            ->scalar('label')->maxLength('label', 120)->notEmptyString('label')
            ->scalar('content')->notEmptyString('content')
            ->nonNegativeInteger('position')->notEmptyString('position')
            ->boolean('active')->notEmptyString('active');
    }
}
