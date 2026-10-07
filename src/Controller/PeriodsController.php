<?php
declare(strict_types=1);

namespace App\Controller;

/** Contrôleur des périodicités. */
/**
 * @property \App\Model\Table\PeriodsTable $Periods
 */
final class PeriodsController extends ReferenceController
{
    /** @return string Alias ORM de la table. */
    protected function referenceAlias(): string
    {
        return 'Periods';
    }

    /** @return string Libellé affiché. */
    protected function referenceLabel(): string
    {
        return __('Périodicités');
    }
}
