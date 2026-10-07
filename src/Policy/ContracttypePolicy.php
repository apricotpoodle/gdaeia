<?php
declare(strict_types=1);

namespace App\Policy;

use Authorization\IdentityInterface;

/** Politique du référentiel des types de contrats. */
final class ContracttypePolicy extends ReferencePolicy
{
    /** @inheritDoc */
    public function canIndex(IdentityInterface $identity): bool
    {
        return parent::canIndex($identity);
    }
}
