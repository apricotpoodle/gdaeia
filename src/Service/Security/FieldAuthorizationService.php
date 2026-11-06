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
                FieldAuthorization::FIELD_TARGET_TYPE => FieldAuthorization::TARGET_TYPE_FIELD,
            ])
            ->all()
            ->toArray();

        $schema = [];
        foreach ($records as $record) {
            $target = $record->get(FieldAuthorization::FIELD_TARGET) ?? $record->get('field');
            $schema[(string)$target] = FieldAuthorization::normalizeAccessLevel(
                (string)$record->get(FieldAuthorization::FIELD_ACCESS_LEVEL),
            );
        }

        return $schema;
    }

    /** Vérifie une autorisation de zone fonctionnelle pour une entité donnée. */
    public function canEditSection(IdentityInterface $identity, string $resource, string $section): bool
    {
        /** @var \App\Model\Entity\User $user */
        $user = $identity->getOriginalData();
        if ($user->get(User::FIELD_ISSUPERUSER)) {
            return true;
        }

        return TableRegistry::getTableLocator()->get('FieldAuthorizations')->find()
            ->where([
                FieldAuthorization::FIELD_ROLE_ID => $user->get(User::FIELD_ROLE_ID),
                FieldAuthorization::FIELD_RESOURCE => $resource,
                FieldAuthorization::FIELD_TARGET_TYPE => FieldAuthorization::TARGET_TYPE_SECTION,
                FieldAuthorization::FIELD_TARGET => $section,
                FieldAuthorization::FIELD_ACCESS_LEVEL => FieldAuthorization::ACCESS_LEVEL_EDIT,
            ])->count() > 0;
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
            $access = $fieldSchema[$field] ?? FieldAuthorization::ACCESS_LEVEL_NONE;

            if ($access !== FieldAuthorization::ACCESS_LEVEL_EDIT) {
                unset($data[$field]);
            }
        }

        return $data;
    }

    /**
     * Filtre une requête pour une ressource selon l'identité courante.
     *
     * Une matrice absente signifie que la ressource n'est pas encore activée
     * dans le déploiement progressif de l'ACL. Dès qu'elle contient au moins
     * une règle, les champs non déclarés sont refusés par défaut.
     *
     * @param \Authorization\IdentityInterface $identity Identité authentifiée.
     * @param string $resource Ressource ORM concernée.
     * @param array<string, mixed> $data Données issues de la requête.
     * @return array<string, mixed> Données filtrées avant le patch ORM.
     */
    public function filterAuthorizedRequestData(
        IdentityInterface $identity,
        string $resource,
        array $data,
    ): array {
        $fieldSchema = $this->getFieldSchema($identity, $resource);

        return $this->filterRequestData($data, $fieldSchema);
    }
}
