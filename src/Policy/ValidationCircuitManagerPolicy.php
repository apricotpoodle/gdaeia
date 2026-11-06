<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\ValidationCircuitManager;
use Authorization\IdentityInterface;

/** Réserve les habilitations locales aux super-administrateurs. */
final class ValidationCircuitManagerPolicy extends AppPolicy
{
    /** Autorise la lecture des désignations à l'administrateur de leurs accès. */
    public function canView(IdentityInterface $identity, ValidationCircuitManager $manager): bool
    {
        return $this->isSuperUser($identity);
    }

    /** Les désignations se remplacent explicitement par retrait puis ajout. */
    public function canEdit(IdentityInterface $identity, ValidationCircuitManager $manager): bool
    {
        return false;
    }

    /** Autorise le retrait d'une désignation. */
    public function canDelete(IdentityInterface $identity, ValidationCircuitManager $manager): bool
    {
        return $this->isSuperUser($identity);
    }
}
