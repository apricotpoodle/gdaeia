<?php
declare(strict_types=1);

namespace App\Controller;

/** Contrôleur des types de contrats. */
/**
 * @property \App\Model\Table\ContracttypesTable $Contracttypes
 */
final class ContracttypesController extends ReferenceController
{
    /** @return string Alias ORM de la table. */
    protected function referenceAlias(): string
    {
        return 'Contracttypes';
    }

    /** @return string Libellé affiché. */
    protected function referenceLabel(): string
    {
        return __('Types de contrats');
    }
}
