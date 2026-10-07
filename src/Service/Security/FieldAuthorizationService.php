<?php
declare(strict_types=1);

namespace App\Service\Security;

use App\Model\Entity\FieldAuthorization;
use App\Model\Entity\User;
use Authorization\IdentityInterface;
use Cake\ORM\TableRegistry;

/**
 * Class FieldAuthorizationService
 *
 * @description Gère et filtre les permissions structurelles au niveau des champs (Field-Level ACL).
 * @package App\Service\Security
 */
class FieldAuthorizationService
{
    /**
     * Récupère la carte des autorisations pour un opérateur et une ressource donnés.
     *
     * @param \Authorization\IdentityInterface $identity
     * @param string $resource
     * @return array<string, string>
     */
    public function getFieldSchema(IdentityInterface $identity, string $resource): array
    {
        /** @var \App\Model\Entity\User $user */
        $user = $identity->getOriginalData();

        if ($user->get(User::FIELD_ISSUPERUSER)) {
            return [];
        }

        $roleId = $user->get(User::FIELD_ROLE_ID);
        $authTable = TableRegistry::getTableLocator()->get('FieldAuthorizations');

        /** @var array<\App\Model\Entity\FieldAuthorization> $records */
        $records = $authTable->find()
            ->where([
                FieldAuthorization::FIELD_ROLE_ID => $roleId,
                FieldAuthorization::FIELD_RESOURCE => $resource,
            ])
            ->all()
            ->toArray();

        $schema = [];
        foreach ($records as $record) {
            $schema[$record->field] = strtoupper($record->access_level);
        }

        return $schema;
    }

    /**
     * Filtre les données soumises par un formulaire (Request Data) en fonction du schéma d'autorisation.
     *
     * @param array<string, mixed> $data Les données brutes issues du POST/JSON.
     * @param array<string, string> $fieldSchema La carte retournée par getFieldSchema.
     * @return array<string, mixed> Les données nettoyées et sécurisées.
     */
    public function filterRequestData(array $data, array $fieldSchema): array
    {
        if (empty($fieldSchema)) {
            return $data;
        }

        foreach ($data as $field => $value) {
            $access = $fieldSchema[$field] ?? 'EDIT';

            if ($access !== 'EDIT') {
                unset($data[$field]);
            }
        }

        return $data;
    }
}
