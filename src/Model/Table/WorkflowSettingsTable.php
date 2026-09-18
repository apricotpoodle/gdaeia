<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}, \App\Model\Entity\WorkflowSetting>
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
final class WorkflowSettingsTable extends Table
{
    /** @inheritDoc */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('workflow_settings');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
    }

    /** @inheritDoc */
    public function validationDefault(Validator $validator): Validator
    {
        return $validator->scalar('name')->maxLength('name', 64)->notEmptyString('name')
            ->scalar('value')->maxLength('value', 255)->notEmptyString('value');
    }
}
