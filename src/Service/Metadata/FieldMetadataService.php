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

    /** Retourne le libellé d'une cible de champ ou de zone. */
    public function targetLabel(string $resource, string $type, string $target): string
    {
        if ($type !== 'section') {
            $definition = $this->definition($resource, $target);
            if ($definition !== null) {
                return $definition['label'];
            }

            $historical = TableRegistry::getTableLocator()->get('FieldDefinitions')->find()
                ->where(['resource' => $resource, 'field' => $target])->first();

            return $historical !== null ? (string)$historical->get(FieldDefinition::FIELD_LABEL) : $target;
        }

        return $this->sectionDefinition($resource, $target)['label'] ?? $target;
    }

    /** Retourne la description d'une cible de champ ou de zone. */
    public function targetDescription(string $resource, string $type, string $target): ?string
    {
        if ($type !== 'section') {
            $definition = $this->definition($resource, $target);
            if ($definition !== null) {
                return $definition['description'];
            }

            $historical = TableRegistry::getTableLocator()->get('FieldDefinitions')->find()
                ->where(['resource' => $resource, 'field' => $target])->first();

            return $historical?->get(FieldDefinition::FIELD_DESCRIPTION) !== null
                ? (string)$historical->get(FieldDefinition::FIELD_DESCRIPTION)
                : null;
        }

        return $this->sectionDefinition($resource, $target)['description'] ?? null;
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
        $definition = $this->all($resource)[$field] ?? null;
        if ($definition !== null) {
            return $definition;
        }

        return null;
    }

    /** @return array{label: string, description: ?string}|null */
    private function sectionDefinition(string $resource, string $target): ?array
    {
        $definition = TableRegistry::getTableLocator()->get('AuthorizationTargets')->find()
            ->where(['resource' => $resource, 'target_type' => 'section', 'target' => $target])
            ->first();
        if ($definition === null) {
            return null;
        }

        return [
            'label' => (string)$definition->get('label'),
            'description' => $definition->get('description') !== null ? (string)$definition->get('description') : null,
        ];
    }
}
