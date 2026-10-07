<?php
declare(strict_types=1);

namespace App\Controller;

/** Contrôleur des temps de travail. */
final class WorktimesController extends ReferenceController
{
    /** @return string Alias ORM de la table. */
    protected function referenceAlias(): string
    {
        return 'Worktimes';
    }

    /** @return string Libellé affiché. */
    protected function referenceLabel(): string
    {
        return __('Temps de travail');
    }
}
