<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * EmailRecipients Model
 *
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\EmailLogsTable> $EmailLogs
 * @method \App\Model\Entity\EmailRecipient newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\EmailRecipient[] newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\EmailRecipient get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\EmailRecipient findOrCreate(\Cake\ORM\Query\SelectQuery<\App\Model\Entity\EmailRecipient>|callable|array<string, mixed> $search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\EmailRecipient patchEntity(\App\Model\Entity\EmailRecipient $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\EmailRecipient[] patchEntities(iterable<\App\Model\Entity\EmailRecipient> $entities, array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\EmailRecipient|false save(\App\Model\Entity\EmailRecipient $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\EmailRecipient saveOrFail(\App\Model\Entity\EmailRecipient $entity, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\EmailRecipient>|false saveMany(iterable<\App\Model\Entity\EmailRecipient> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\EmailRecipient> saveManyOrFail(iterable<\App\Model\Entity\EmailRecipient> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\EmailRecipient>|false deleteMany(iterable<\App\Model\Entity\EmailRecipient> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\EmailRecipient> deleteManyOrFail(iterable<\App\Model\Entity\EmailRecipient> $entities, array<string, mixed> $options = [])
 * @extends \Cake\ORM\Table<array{}, \App\Model\Entity\EmailRecipient>
 * @method bool delete(\App\Model\Entity\EmailRecipient $entity, array<string, mixed> $options = [])
 * @method bool deleteOrFail(\App\Model\Entity\EmailRecipient $entity, array<string, mixed> $options = [])
 */
class EmailRecipientsTable extends Table
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

        $this->setTable('email_recipients');
        $this->setDisplayField('recipient_email');
        $this->setPrimaryKey('id');

        $this->belongsTo('EmailLogs', [
            'foreignKey' => 'email_log_id',
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
            ->integer('email_log_id')
            ->notEmptyString('email_log_id');

        $validator
            ->scalar('recipient_email')
            ->maxLength('recipient_email', 255)
            ->requirePresence('recipient_email', 'create')
            ->notEmptyString('recipient_email');

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
        $rules->add($rules->existsIn(['email_log_id'], 'EmailLogs'), ['errorField' => 'email_log_id']);

        return $rules;
    }
}
