<?php
declare(strict_types=1);

namespace App\Policy;

use Authorization\IdentityInterface;
use Cake\Datasource\EntityInterface;

/** Autorisations de gestion des codes CGR rattachés aux départements. */
final class CgrCodePolicy extends AdministrationPolicy
{
    /** Autorise l’accès aux codes CGR d’un département. */
    public function canIndex(IdentityInterface $identity, EntityInterface $code): bool
    {
        return $this->allowsAdministration($identity, 'references.read') && $code->get('active') === true;
    }

    /** Autorise l’ajout d’un code CGR. */
    public function canAdd(IdentityInterface $identity, EntityInterface $code): bool
    {
        return $this->allowsAdministration($identity, 'references.create');
    }

    /** Autorise la consultation d’un code actif. */
    public function canView(IdentityInterface $identity, EntityInterface $code): bool
    {
        return $this->allowsAdministration($identity, 'references.read') && $code->get('active') === true;
    }

    /** Autorise l’association ou la dissociation d’un code CGR actif. */
    public function canAssociate(IdentityInterface $identity, EntityInterface $code): bool
    {
        return $this->isSuperUserOrAdmin($identity) && $code->get('active') === true;
    }

    /** Alias de suppression conservé pour le contrat CRUD standard. */
    public function canDelete(IdentityInterface $identity, EntityInterface $code): bool
    {
        return $this->canDeactivate($identity, $code);
    }

    /** Autorise la modification d’un code CGR non système. */
    public function canEdit(IdentityInterface $identity, EntityInterface $code): bool
    {
        return $this->allowsAdministration($identity, 'references.update') && !$code->get('is_system');
    }

    /** Autorise la désactivation d’un code CGR non système. */
    public function canDeactivate(IdentityInterface $identity, EntityInterface $code): bool
    {
        return $this->allowsAdministration($identity, 'references.delete')
            && $code->get('active') === true
            && !$code->get('is_system');
    }
}
