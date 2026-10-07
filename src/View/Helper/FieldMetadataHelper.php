<?php
declare(strict_types=1);

namespace App\View\Helper;

use App\Service\Metadata\FieldMetadataService;
use Cake\View\Helper;
use Cake\View\View;

/**
 * Expose le dictionnaire des champs aux templates et aux modules de page.
 *
 * @extends \Cake\View\Helper<\Cake\View\View>
 */
final class FieldMetadataHelper extends Helper
{
    private FieldMetadataService $service;

    /** @inheritDoc */
    public function __construct(View $View, array $config = [])
    {
        parent::__construct($View, $config);
        $this->service = new FieldMetadataService();
    }

    /** Retourne le libellé métier d'un champ. */
    public function label(string $resource, string $field): string
    {
        return h($this->service->label($resource, $field));
    }

    /** Retourne la description métier d'un champ. */
    public function description(string $resource, string $field): ?string
    {
        $description = $this->service->description($resource, $field);

        return $description === null ? null : h($description);
    }

    /** @return array<string, array{label: string, description: ?string}> */
    public function all(string $resource): array
    {
        return $this->service->all($resource);
    }
}
