<?php
declare(strict_types=1);

namespace App\Controller;

/** Contrôleur des motifs de recrutement. */
final class HiringreasonsController extends ReferenceController
{
    /** @return string Alias ORM de la table. */
    protected function referenceAlias(): string
    {
        return 'Hiringreasons';
    }

    /** @return string Libellé affiché. */
    protected function referenceLabel(): string
    {
        return __('Motifs de recrutement');
    }
}
