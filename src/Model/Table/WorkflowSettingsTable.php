<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\WorkflowSetting;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}, \App\Model\Entity\WorkflowSetting>
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @method \App\Model\Entity\WorkflowSetting newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\WorkflowSetting[] newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\WorkflowSetting get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\WorkflowSetting findOrCreate(\Cake\ORM\Query\SelectQuery<\App\Model\Entity\WorkflowSetting>|callable|array<string, mixed> $search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\WorkflowSetting patchEntity(\App\Model\Entity\WorkflowSetting $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\WorkflowSetting[] patchEntities(iterable<\App\Model\Entity\WorkflowSetting> $entities, array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\WorkflowSetting|false save(\App\Model\Entity\WorkflowSetting $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\WorkflowSetting saveOrFail(\App\Model\Entity\WorkflowSetting $entity, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\WorkflowSetting>|false saveMany(iterable<\App\Model\Entity\WorkflowSetting> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\WorkflowSetting> saveManyOrFail(iterable<\App\Model\Entity\WorkflowSetting> $entities, array<string, mixed> $options = [])
 * @method bool delete(\App\Model\Entity\WorkflowSetting $entity, array<string, mixed> $options = [])
 * @method bool deleteOrFail(\App\Model\Entity\WorkflowSetting $entity, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\WorkflowSetting>|false deleteMany(iterable<\App\Model\Entity\WorkflowSetting> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\WorkflowSetting> deleteManyOrFail(iterable<\App\Model\Entity\WorkflowSetting> $entities, array<string, mixed> $options = [])
 */
final class WorkflowSettingsTable extends Table
{
    /** @inheritDoc */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('workflow_settings');
        $this->setPrimaryKey(WorkflowSetting::FIELD_ID);
        $this->addBehavior('Timestamp');
    }

    /** @inheritDoc */
    public function validationDefault(Validator $validator): Validator
    {
        return $validator->scalar(WorkflowSetting::FIELD_NAME)->maxLength(WorkflowSetting::FIELD_NAME, 64)->notEmptyString(WorkflowSetting::FIELD_NAME)
            ->scalar(WorkflowSetting::FIELD_VALUE)->maxLength(WorkflowSetting::FIELD_VALUE, 255)->notEmptyString(WorkflowSetting::FIELD_VALUE);
    }
}
