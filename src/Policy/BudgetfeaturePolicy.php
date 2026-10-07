<?php
declare(strict_types=1);

namespace App\Policy;

use Authorization\IdentityInterface;

/** Politique du référentiel des caractéristiques budgétaires. */
final class BudgetfeaturePolicy extends ReferencePolicy
{
    /** @inheritDoc */
    public function canIndex(IdentityInterface $identity): bool
    {
        return parent::canIndex($identity);
    }
}
