<?php
declare(strict_types=1);

namespace App\Controller\Api;

/** API des temps de travail. */
final class WorktimesController extends ReferenceController
{
    /** @return string Alias ORM de la table. */
    protected function referenceAlias(): string
    {
        return 'Worktimes';
    }
}
