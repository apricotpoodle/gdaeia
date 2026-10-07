<?php
declare(strict_types=1);

namespace App\Controller;

/** Contrôleur des réponses Oui / Non. */
final class YesnosController extends ReferenceController
{
    /** @return string Alias ORM de la table. */
    protected function referenceAlias(): string
    {
        return 'Yesnos';
    }

    /** @return string Libellé affiché. */
    protected function referenceLabel(): string
    {
        return __('Réponses Oui / Non');
    }
}
