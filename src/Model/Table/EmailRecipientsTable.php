<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * EmailRecipients Model
 *
 * @property \App\Model\Table\EmailLogsTable&\Cake\ORM\Association\BelongsTo $EmailLogs
 *
 * @method \App\Model\Entity\EmailRecipient newEmptyEntity()
 * @method \App\Model\Entity\EmailRecipient newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\EmailRecipient> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\EmailRecipient get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\EmailRecipient findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\EmailRecipient patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\EmailRecipient> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\EmailRecipient|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\EmailRecipient saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\EmailRecipient>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\EmailRecipient>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\EmailRecipient>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\EmailRecipient> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\EmailRecipient>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\EmailRecipient>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\EmailRecipient>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\EmailRecipient> deleteManyOrFail(iterable $entities, array $options = [])
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
