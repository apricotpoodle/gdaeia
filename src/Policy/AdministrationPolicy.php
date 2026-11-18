<?php
declare(strict_types=1);

namespace App\Policy;

use App\Service\Security\PermissionResolver;
use Authorization\IdentityInterface;

/** Vérifie les actions d’administration sans périmètre organisationnel. */
abstract class AdministrationPolicy extends AppPolicy
{
    /** Construit la Policy avec les attributions persistées ou injectées. */
    public function __construct(private readonly PermissionResolver $permissions = new PermissionResolver())
    {
    }

    /** Conserve le refus des identités invalides et le contournement super-administrateur. */
    protected function allowsAdministration(IdentityInterface $identity, string $code): bool
    {
        $user = $this->getValidUser($identity);

        return $user !== null && $this->permissions->allows($user, $code, PermissionResolver::SCOPE_UNSCOPED);
    }
}
