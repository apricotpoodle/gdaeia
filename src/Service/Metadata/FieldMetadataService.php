<?php
declare(strict_types=1);

namespace App\Service\Metadata;

use App\Model\Entity\FieldDefinition;
use Cake\ORM\TableRegistry;

/** Fournit les métadonnées d'affichage des champs depuis le référentiel SQL. */
final class FieldMetadataService implements FieldMetadataProviderInterface
{
    /**
     * @var array<string, array<string, array{label: string, description: ?string}>>
     */
    private array $cache = [];

    /** Retourne le libellé métier, ou le nom technique si le champ est inconnu. */
    public function label(string $resource, string $field): string
    {
        return $this->definition($resource, $field)['label'] ?? $field;
    }

    /** Retourne la description métier d'un champ, si elle existe. */
    public function description(string $resource, string $field): ?string
    {
        return $this->definition($resource, $field)['description'] ?? null;
    }

    /** @return array<string, array{label: string, description: ?string}> */
    public function all(string $resource): array
    {
        if (!isset($this->cache[$resource])) {
            $table = TableRegistry::getTableLocator()->get('FieldDefinitions');
            /** @var array<string, array{label: string, description: ?string}> $definitions */
            $definitions = [];
            $query = $table->find()
                ->select([
                    FieldDefinition::FIELD_FIELD,
                    FieldDefinition::FIELD_LABEL,
                    FieldDefinition::FIELD_DESCRIPTION,
                ])
                ->where([
                    FieldDefinition::FIELD_RESOURCE => $resource,
                    FieldDefinition::FIELD_ACTIVE => true,
                ])
                ->orderByAsc(FieldDefinition::FIELD_POSITION);
            foreach ($query->all() as $entity) {
                $field = (string)$entity->get(FieldDefinition::FIELD_FIELD);
                $definitions[$field] = [
                    'label' => (string)$entity->get(FieldDefinition::FIELD_LABEL),
                    'description' => $entity->get(FieldDefinition::FIELD_DESCRIPTION) !== null
                        ? (string)$entity->get(FieldDefinition::FIELD_DESCRIPTION)
                        : null,
                ];
            }
            $this->cache[$resource] = $definitions;
        }

        return $this->cache[$resource];
    }

    /** @return array{label: string, description: ?string}|null */
    private function definition(string $resource, string $field): ?array
    {
        return $this->all($resource)[$field] ?? null;
    }
}
