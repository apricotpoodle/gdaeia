<?php

declare(strict_types=1);

namespace App\Service\Security;

use Cake\ORM\TableRegistry;
use Authorization\IdentityInterface;

/**
 * Class FieldAuthorizationService
 * @description Gère et filtre les permissions structurelles au niveau des champs (Field-Level ACL).
 * @package App\Service\Security
 */
class FieldAuthorizationService
{
    /**
     * Récupère la carte des autorisations pour un opérateur et une ressource donnés.
     * Retourne un tableau associatif : ['nom_champ' => 'EDIT'|'VIEW'|'NONE']
     *
     * @param \Authorization\IdentityInterface $identity L'opérateur connecté.
     * @param string $resource Le nom de la ressource (ex: 'Users').
     * @return array<string, string>
     */
    public function getFieldSchema(IdentityInterface $identity, string $resource): array
    {
        /** @var \App\Model\Entity\User $user */
        $user = $identity->getOriginalData();

        // Principe KISS / Failsafe : Le Super Admin a TOUS les droits d'édition par défaut
        if ($user->get('issuperuser')) {
            return [];
        }

        $roleId = $user->get('role_id');
        $authTable = TableRegistry::getTableLocator()->get('FieldAuthorizations');

        /** @var array<\App\Model\Entity\FieldAuthorization> $records */
        $records = $authTable->find()
            ->where([
                'role_id' => $roleId,
                'resource' => $resource
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
     * Filtre les données soumises par un formulaire (Request Data) en fonction du schéma
     * d'autorisation pour empêcher l'injection de champs non autorisés (Mass-Assignment protection).
     *
     * @param array $data Les données brutes issues du POST.
     * @param array<string, string> $fieldSchema La carte retournée par getFieldSchema.
     * @return array Les données nettoyées et sécurisées.
     */
    public function filterRequestData(array $data, array $fieldSchema): array
    {
        foreach ($data as $field => $value) {
            $access = $fieldSchema[$field] ?? 'EDIT'; // Si non défini, on considère EDIT par défaut (ou inversement selon politique)

            if ($access !== 'EDIT') {
                unset($data[$field]); // Purge immédiate du champ non autorisé
            }
        }
        return $data;
    }
}
