<?php
declare(strict_types=1);

namespace App\Service\Metadata;

/** Contrat de lecture des métadonnées d'affichage des champs. */
interface FieldMetadataProviderInterface
{
    /** Retourne le libellé métier ou le nom technique en repli. */
    public function label(string $resource, string $field): string;

    /** Retourne la description métier, si elle existe. */
    public function description(string $resource, string $field): ?string;

    /** @return array<string, array{label: string, description: ?string}> */
    public function all(string $resource): array;
}
