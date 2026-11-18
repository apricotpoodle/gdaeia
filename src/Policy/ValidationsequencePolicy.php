<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Validationsequence;
use App\Service\Security\PermissionResolver;
use Authorization\IdentityInterface;

/** Politique de l'administration de la configuration des validations. */
class ValidationsequencePolicy extends AppPolicy
{
    /** Construit la Policy avec le résolveur commun. */
    public function __construct(private readonly PermissionResolver $permissions = new PermissionResolver())
    {
    }

    /** @param \Authorization\IdentityInterface $identity @return bool */
    public function canIndex(IdentityInterface $identity): bool
    {
        $user = $this->getValidUser($identity);

        return $user !== null && $this->permissions->allowsDepartment($user, 'validationsequences.read');
    }

    /** Autorise la consultation d’une séquence dans un département direct. */
    public function canView(IdentityInterface $identity, Validationsequence $sequence): bool
    {
        $user = $this->getValidUser($identity);
        $departmentId = $sequence->get('department_id');

        return $user !== null && $departmentId !== null
            && $this->permissions->allowsDepartment($user, 'validationsequences.read', (int)$departmentId);
    }

    /** Autorise l’ajout dans les départements directement affectés. */
    public function canAdd(IdentityInterface $identity, Validationsequence $sequence): bool
    {
        return $this->canMutate($identity, $sequence, 'create');
    }

    /** Autorise la modification dans les départements directement affectés. */
    public function canEdit(IdentityInterface $identity, Validationsequence $sequence): bool
    {
        return $this->canMutate($identity, $sequence, 'update');
    }

    /** Autorise le retrait dans les départements directement affectés. */
    public function canDelete(IdentityInterface $identity, Validationsequence $sequence): bool
    {
        return $this->canMutate($identity, $sequence, 'delete');
    }

    /** Alias historique de modification ; les API utilisent les actions CRUD distinctes. */
    public function canManage(IdentityInterface $identity, Validationsequence $sequence): bool
    {
        return $this->canEdit($identity, $sequence);
    }

    /** Exige assigned pour l’action, puis vérifie l’affectation départementale concrète. */
    private function canMutate(IdentityInterface $identity, Validationsequence $sequence, string $action): bool
    {
        $user = $this->getValidUser($identity);
        $code = 'validationsequences.' . $action;
        $departmentId = $sequence->get('department_id');

        return $user !== null
            && $this->permissions->allows($user, $code, PermissionResolver::SCOPE_ASSIGNED)
            && $this->permissions->allowsDepartment(
                $user,
                $code,
                $departmentId === null ? null : (int)$departmentId,
                allowAll: false,
            );
    }
}
