<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\EmailLog;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * EmailLogs Model
 *
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\EmailRecipientsTable> $EmailRecipients
 * @method \App\Model\Entity\EmailLog newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\EmailLog[] newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\EmailLog get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\EmailLog findOrCreate(\Cake\ORM\Query\SelectQuery<\App\Model\Entity\EmailLog>|callable|array<string, mixed> $search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\EmailLog patchEntity(\App\Model\Entity\EmailLog $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\EmailLog[] patchEntities(iterable<\App\Model\Entity\EmailLog> $entities, array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\EmailLog|false save(\App\Model\Entity\EmailLog $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\EmailLog saveOrFail(\App\Model\Entity\EmailLog $entity, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\EmailLog>|false saveMany(iterable<\App\Model\Entity\EmailLog> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\EmailLog> saveManyOrFail(iterable<\App\Model\Entity\EmailLog> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\EmailLog>|false deleteMany(iterable<\App\Model\Entity\EmailLog> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\EmailLog> deleteManyOrFail(iterable<\App\Model\Entity\EmailLog> $entities, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}, \App\Model\Entity\EmailLog>
 * @method bool delete(\App\Model\Entity\EmailLog $entity, array<string, mixed> $options = [])
 * @method bool deleteOrFail(\App\Model\Entity\EmailLog $entity, array<string, mixed> $options = [])
 */
class EmailLogsTable extends Table
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

        $this->setTable('email_logs');
        $this->setDisplayField(EmailLog::FIELD_SUBJECT);
        $this->setPrimaryKey(EmailLog::FIELD_ID);

        $this->addBehavior('Timestamp');

        $this->hasMany('EmailRecipients', [
            'foreignKey' => 'email_log_id',
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
            ->scalar(EmailLog::FIELD_SUBJECT)
            ->maxLength(EmailLog::FIELD_SUBJECT, 255)
            ->requirePresence(EmailLog::FIELD_SUBJECT, 'create')
            ->notEmptyString(EmailLog::FIELD_SUBJECT);

        $validator
            ->scalar(EmailLog::FIELD_CONTENT_TEXT)
            ->allowEmptyString(EmailLog::FIELD_CONTENT_TEXT);

        $validator
            ->scalar(EmailLog::FIELD_CONTENT_HTML)
            ->allowEmptyString(EmailLog::FIELD_CONTENT_HTML);

        $validator
            ->scalar(EmailLog::FIELD_ERROR_MESSAGE)
            ->allowEmptyString(EmailLog::FIELD_ERROR_MESSAGE);

        return $validator;
    }
}
