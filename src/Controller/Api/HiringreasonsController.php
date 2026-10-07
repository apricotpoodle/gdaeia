<?php
declare(strict_types=1);

namespace App\Controller\Api;

/** API des motifs de recrutement. */
final class HiringreasonsController extends ReferenceController
{
    /** @return string Alias ORM de la table. */
    protected function referenceAlias(): string
    {
        return 'Hiringreasons';
    }
}
