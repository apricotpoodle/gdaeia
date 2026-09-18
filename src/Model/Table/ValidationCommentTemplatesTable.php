<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}, \App\Model\Entity\ValidationCommentTemplate>
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
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
