<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\AuthorizationTarget;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/** Référentiel des cibles sélectionnables par l'administration ACL. */
final class AuthorizationTargetsTable extends Table
{
    /** @inheritDoc */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('authorization_targets');
        $this->setPrimaryKey(AuthorizationTarget::FIELD_ID);
        $this->setDisplayField(AuthorizationTarget::FIELD_LABEL);
        $this->addBehavior('Timestamp');
    }

    /** @inheritDoc */
    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->scalar('resource')->maxLength('resource', 80)->notEmptyString('resource')
            ->scalar('target_type')->inList('target_type', AuthorizationTarget::TYPES)->notEmptyString('target_type')
            ->scalar('target')->maxLength('target', 80)->notEmptyString('target')
            ->scalar('label')->maxLength('label', 160)->notEmptyString('label')
            ->allowEmptyString('description')
            ->boolean('active')->requirePresence('active', 'create')
            ->nonNegativeInteger('position')->requirePresence('position', 'create');
    }
}
