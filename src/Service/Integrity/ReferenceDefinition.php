<?php
declare(strict_types=1);

namespace App\Service\Integrity;

/** Décrit une référence persistée contrôlable par l’audit. */
final readonly class ReferenceDefinition
{
    /**
     * @param 'exists'|'active'|'historical' $targetPolicy Politique appliquée à la cible.
     */
    public function __construct(
        public string $id,
        public string $sourceTable,
        public string $sourceColumn,
        public string $targetTable,
        public string $targetColumn = 'id',
        public string $sourceKey = 'id',
        public bool $nullable = false,
        public string $targetPolicy = 'exists',
        public ?string $sourceDeletedColumn = null,
    ) {
    }
}
