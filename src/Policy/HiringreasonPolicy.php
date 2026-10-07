<?php
declare(strict_types=1);

namespace App\Policy;

use Authorization\IdentityInterface;

/** Politique du référentiel des motifs de recrutement. */
final class HiringreasonPolicy extends ReferencePolicy
{
    /** @inheritDoc */
    public function canIndex(IdentityInterface $identity): bool
    {
        return parent::canIndex($identity);
    }
}
