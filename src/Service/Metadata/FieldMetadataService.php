<?php
declare(strict_types=1);

namespace App\Service\Metadata;

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
                ->select(['field', 'label', 'description'])
                ->where(['resource' => $resource, 'active' => true])
                ->orderByAsc('position');
            foreach ($query->all() as $entity) {
                $field = (string)$entity->get('field');
                $definitions[$field] = [
                    'label' => (string)$entity->get('label'),
                    'description' => $entity->get('description') !== null
                        ? (string)$entity->get('description')
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
