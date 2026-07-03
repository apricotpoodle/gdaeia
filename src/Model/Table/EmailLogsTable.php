<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * EmailLogs Model
 *
 * @property \App\Model\Table\EmailRecipientsTable&\Cake\ORM\Association\HasMany $EmailRecipients
 *
 * @method \App\Model\Entity\EmailLog newEmptyEntity()
 * @method \App\Model\Entity\EmailLog newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\EmailLog> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\EmailLog get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\EmailLog findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\EmailLog patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\EmailLog> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\EmailLog|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\EmailLog saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\EmailLog>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\EmailLog>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\EmailLog>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\EmailLog> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\EmailLog>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\EmailLog>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\EmailLog>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\EmailLog> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
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
        $this->setDisplayField('subject');
        $this->setPrimaryKey('id');

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
            ->scalar('subject')
            ->maxLength('subject', 255)
            ->requirePresence('subject', 'create')
            ->notEmptyString('subject');

        $validator
            ->scalar('content_text')
            ->allowEmptyString('content_text');

        $validator
            ->scalar('content_html')
            ->allowEmptyString('content_html');

        $validator
            ->scalar('error_message')
            ->allowEmptyString('error_message');

        return $validator;
    }
}
