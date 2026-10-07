<?php
declare(strict_types=1);

namespace App\Controller\Api;

/** API des caractéristiques budgétaires. */
/**
 * @property \App\Model\Table\BudgetfeaturesTable $Budgetfeatures
 */
final class BudgetfeaturesController extends ReferenceController
{
    /** @return string Alias ORM de la table. */
    protected function referenceAlias(): string
    {
        return 'Budgetfeatures';
    }
}
