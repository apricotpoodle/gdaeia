<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

final class ValidationCommentTemplatesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('validation_comment_templates');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator): Validator
    {
        return $validator->inList('decision', ['accepter', 'refuser'])->notEmptyString('decision')
            ->scalar('label')->maxLength('label', 120)->notEmptyString('label')
            ->scalar('content')->notEmptyString('content')
            ->nonNegativeInteger('position')->notEmptyString('position')
            ->boolean('active')->notEmptyString('active');
    }
}
