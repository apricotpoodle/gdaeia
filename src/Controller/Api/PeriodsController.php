<?php
declare(strict_types=1);

namespace App\Controller\Api;

/** API des périodicités. */
final class PeriodsController extends ReferenceController
{
    /** @return string Alias ORM de la table. */
    protected function referenceAlias(): string
    {
        return 'Periods';
    }
}
