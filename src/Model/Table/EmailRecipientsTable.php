<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\EmailRecipient;
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
        $this->setDisplayField(EmailRecipient::FIELD_RECIPIENT_EMAIL);
        $this->setPrimaryKey(EmailRecipient::FIELD_ID);

        $this->belongsTo('EmailLogs', [
            'foreignKey' => EmailRecipient::FIELD_EMAIL_LOG_ID,
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
            ->integer(EmailRecipient::FIELD_EMAIL_LOG_ID)
            ->notEmptyString(EmailRecipient::FIELD_EMAIL_LOG_ID);

        $validator
            ->scalar(EmailRecipient::FIELD_RECIPIENT_EMAIL)
            ->maxLength(EmailRecipient::FIELD_RECIPIENT_EMAIL, 255)
            ->requirePresence(EmailRecipient::FIELD_RECIPIENT_EMAIL, 'create')
            ->notEmptyString(EmailRecipient::FIELD_RECIPIENT_EMAIL);

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
        $rules->add($rules->existsIn([EmailRecipient::FIELD_EMAIL_LOG_ID], 'EmailLogs'), [
            'errorField' => EmailRecipient::FIELD_EMAIL_LOG_ID,
        ]);

        return $rules;
    }
}
