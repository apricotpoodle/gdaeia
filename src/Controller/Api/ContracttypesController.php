<?php
declare(strict_types=1);

namespace App\Controller\Api;

/** API des types de contrats. */
final class ContracttypesController extends ReferenceController
{
    /** @return string Alias ORM de la table. */
    protected function referenceAlias(): string
    {
        return 'Contracttypes';
    }
}
