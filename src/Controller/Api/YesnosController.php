<?php
declare(strict_types=1);

namespace App\Controller\Api;

/** API des réponses Oui / Non. */
/**
 * @property \App\Model\Table\YesnosTable $Yesnos
 */
final class YesnosController extends ReferenceController
{
    /** @return string Alias ORM de la table. */
    protected function referenceAlias(): string
    {
        return 'Yesnos';
    }
}
