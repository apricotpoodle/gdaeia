<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/** Référentiel des libellés et descriptions de champs métier. */
final class FieldDefinitionsTable extends Table
{
    /** @inheritDoc */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('field_definitions');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
    }

    /** @inheritDoc */
    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->scalar('resource')->maxLength('resource', 80)->notEmptyString('resource')
            ->scalar('field')->maxLength('field', 80)->notEmptyString('field')
            ->scalar('label')->maxLength('label', 160)->notEmptyString('label')
            ->allowEmptyString('description')
            ->boolean('active')->requirePresence('active', 'create')
            ->nonNegativeInteger('position')->requirePresence('position', 'create');
    }
}
