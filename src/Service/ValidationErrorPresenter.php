<?php
declare(strict_types=1);

namespace App\Service;

use App\Service\Metadata\FieldMetadataProviderInterface;
use App\Service\Metadata\FieldMetadataService;
use Cake\Datasource\EntityInterface;

/** Présente les erreurs de validation ORM sans dépendre du canal Web ou API. */
final class ValidationErrorPresenter
{
    private FieldMetadataProviderInterface $metadata;

    /** @param \App\Service\Metadata\FieldMetadataProviderInterface|null $metadata Fournisseur injectable. */
    public function __construct(?FieldMetadataProviderInterface $metadata = null)
    {
        $this->metadata = $metadata ?? new FieldMetadataService();
    }

    /**
     * @return array{summary: string, errors: list<array{field: string, label: string, reason: string}>}
     */
    public function present(EntityInterface $entity, string $resource): array
    {
        $errors = [];
        $this->collect($entity->getErrors(), [], false, $resource, $errors);

        $summary = $errors === []
            ? __('Aucun détail de validation n’a été retourné.')
            : __('Champ « {0} » : {1}', $errors[0]['label'], $errors[0]['reason']);

        return ['summary' => $summary, 'errors' => $errors];
    }

    /**
     * @param array<array-key, mixed> $nodes Erreurs d'une entité ou d'un champ.
     * @param list<string> $path Chemin de l'association ou du champ courant.
     * @param bool $isField Indique si les clés de chaînes sont des règles de validation.
     * @param string $resource Ressource utilisée pour résoudre les libellés.
     * @param list<array{field: string, label: string, reason: string}> $results Erreurs présentées.
     */
    private function collect(array $nodes, array $path, bool $isField, string $resource, array &$results): void
    {
        foreach ($nodes as $key => $value) {
            if ($isField && is_string($value)) {
                $this->append($path, $value, $resource, $results);
                continue;
            }

            $nextPath = [...$path, (string)$key];
            if (is_string($value)) {
                $this->append($nextPath, $value, $resource, $results);
            } elseif (is_array($value)) {
                $this->collect($value, $nextPath, !is_int($key), $resource, $results);
            }
        }
    }

    /**
     * @param list<string> $path Chemin technique du champ.
     * @param list<array{field: string, label: string, reason: string}> $results Erreurs présentées.
     */
    private function append(array $path, string $reason, string $resource, array &$results): void
    {
        $field = implode('.', $path);
        $name = (string)end($path);
        $label = $this->metadata->label($resource, $name);
        if ($label === $name) {
            $label = __('Champ non répertorié ({0})', $name);
        }

        $results[] = [
            'field' => $field,
            'label' => __($label),
            'reason' => $reason,
        ];
    }
}
