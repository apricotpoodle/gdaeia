<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\User;
use App\Policy\Trait\ImpersonationCheckTrait;
use Authorization\IdentityInterface;

/**
 * Base class for all application policies.
 */
abstract class AppPolicy
{
    use ImpersonationCheckTrait;

    /**
     * Méthode utilitaire DRY : Extrait et garantit le type de l'identité connectée.
     * Si l'identité n'est pas un humain (ex: un démon système ou une API), renvoie null.
     *
     * @param \Authorization\IdentityInterface $identity
     * @return \App\Model\Entity\User|null
     */
    protected function getValidUser(IdentityInterface $identity): ?User
    {
        $user = $identity->getOriginalData();

        return $user instanceof User ? $user : null;
    }
}
