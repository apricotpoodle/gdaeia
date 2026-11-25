<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Applicationform;
use App\Model\Entity\User;
use App\Model\Entity\UserDepartment;
use App\Model\Entity\ValidationWorkflowRun;
use App\Service\Security\PermissionResolver;
use App\Service\Workflow\ApplicationformValidationWorkflow;
use Authorization\IdentityInterface;
use Cake\ORM\TableRegistry;

/**
 * Class ApplicationformPolicy
 *
 * Politiques d'accès pour les demandes de recrutement (Applicationforms).
 */
class ApplicationformPolicy extends AppPolicy
{
    /** Construit la Policy avec le résolveur commun. */
    public function __construct(private readonly PermissionResolver $permissions = new PermissionResolver())
    {
    }

    /**
     * Autorisation pour la liste (index)
     *
     * @param \Authorization\IdentityInterface $identity
     * @return bool
     */
    public function canIndex(IdentityInterface $identity): bool
    {
        $user = $this->getValidUser($identity);

        return $user !== null && $this->permissions->allowsEntry($user, 'applicationforms.read');
    }

    /** L'accès à la synthèse des cycles bloqués est réservé aux administrateurs. */
    public function canViewBlockedValidations(IdentityInterface $identity, Applicationform $applicationform): bool
    {
        $user = $this->getValidUser($identity);

        return $user !== null && ($user->isSuperUser() || $user->hasRole(User::ROLE_ADMIN));
    }

    /**
     * Autorisation pour l'affichage d'un élément (view)
     *
     * @param \Authorization\IdentityInterface $identity
     * @param \App\Model\Entity\Applicationform $applicationform
     * @return bool
     */
    public function canView(IdentityInterface $identity, Applicationform $applicationform): bool
    {
        $user = $this->getValidUser($identity);

        return $user !== null
            && $this->permissions->allowsApplicationform($user, 'applicationforms.read', $applicationform);
    }

    /** Visibilité historique conservée pour les actions hors matrice. */
    private function canViewHistorically(IdentityInterface $identity, Applicationform $applicationform): bool
    {
        $user = $this->getValidUser($identity);
        if (!$user) {
            return false;
        }

        if (
            $user->get(User::FIELD_ISSUPERUSER)
            || $applicationform->get(Applicationform::FIELD_USER_ID) === $user->get(User::FIELD_ID)
        ) {
            return true;
        }
        if ($applicationform->get(Applicationform::FIELD_DEPARTMENT_ID) === null) {
            return false;
        }

        return TableRegistry::getTableLocator()->get('UserDepartments')->find()
            ->where([
                'UserDepartments.' . UserDepartment::FIELD_USER_ID => $user->get(User::FIELD_ID),
                'UserDepartments.' . UserDepartment::FIELD_DEPARTMENT_ID => $applicationform->get(
                    Applicationform::FIELD_DEPARTMENT_ID,
                ),
            ])
            ->count() > 0;
    }

    /**
     * Autorisation de produire le PDF d'une demande.
     *
     * La production reprend exactement le périmètre de consultation de la
     * fiche afin qu'un lien direct ne permette pas de contourner la Policy.
     *
     * @param \Authorization\IdentityInterface $identity Identité courante.
     * @param \App\Model\Entity\Applicationform $applicationform Demande ciblée.
     * @return bool Vrai si la fiche peut être exportée.
     */
    public function canViewpdf(IdentityInterface $identity, Applicationform $applicationform): bool
    {
        return $this->canView($identity, $applicationform);
    }

    /**
     * Autorisation pour l'ajout (add)
     *
     * @param \Authorization\IdentityInterface $identity
     * @param \App\Model\Entity\Applicationform $applicationform
     * @return bool
     */
    public function canAdd(IdentityInterface $identity, Applicationform $applicationform): bool
    {
        $user = $this->getValidUser($identity);
        if (!$user) {
            return false;
        }

        return $this->canCreateApplicationform($user, $applicationform);
    }

    /**
     * Autorisation de créer une nouvelle DAE à partir d'une DAE visible.
     *
     * @param \Authorization\IdentityInterface $identity Identité courante.
     * @param \App\Model\Entity\Applicationform $applicationform DAE source.
     * @return bool Vrai si la source est visible et l'utilisateur connecté.
     */
    public function canDuplicate(IdentityInterface $identity, Applicationform $applicationform): bool
    {
        $user = $this->getValidUser($identity);

        return $user !== null
            && $this->permissions->allows($user, 'applicationforms.create', PermissionResolver::SCOPE_SELF)
            && $this->canView($identity, $applicationform);
    }

    /**
     * Autorisation pour l'édition (edit)
     *
     * @param \Authorization\IdentityInterface $identity
     * @param \App\Model\Entity\Applicationform $applicationform
     * @return bool
     */
    public function canEdit(IdentityInterface $identity, Applicationform $applicationform): bool
    {
        $user = $this->getValidUser($identity);
        if (!$user || $this->isArchived($applicationform)) {
            return false;
        }

        if ($applicationform->get(Applicationform::FIELD_ID) === null) {
            return $this->canCreateApplicationform($user, $applicationform);
        }

        if ($user->get(User::FIELD_ISSUPERUSER)) {
            return true;
        }

        if (!$this->permissions->allowsApplicationform($user, 'applicationforms.update', $applicationform)) {
            return false;
        }

        if (!$this->canView($identity, $applicationform)) {
            return false;
        }

        if ($this->hasWorkflow($applicationform)) {
            if ((int)$applicationform->get(Applicationform::FIELD_USER_ID) === (int)$user->get(User::FIELD_ID)) {
                return false;
            }

            return (new ApplicationformValidationWorkflow())->canEditDuringActiveStep($applicationform, $user);
        }

        return $applicationform->get(Applicationform::FIELD_USER_ID) === $user->get(User::FIELD_ID)
            || in_array($user->get(User::FIELD_ROLE_ID), Applicationform::ALLOWED_ROLES_FOR_EDIT);
    }

    /** Détermine si un cycle de validation existe pour la demande. */
    private function hasWorkflow(Applicationform $applicationform): bool
    {
        if ($applicationform->get(Applicationform::FIELD_ID) === null) {
            return false;
        }

        return TableRegistry::getTableLocator()->get('ValidationWorkflowRuns')->find()
            ->where([
                ValidationWorkflowRun::FIELD_APPLICATIONFORM_ID => $applicationform->get(Applicationform::FIELD_ID),
            ])
            ->count() > 0;
    }

    /** Le créateur ou un Admin visible peut soumettre une AF au cycle. */
    public function canLaunchValidation(IdentityInterface $identity, Applicationform $applicationform): bool
    {
        $user = $this->getValidUser($identity);
        if (
            $user === null
            || $this->isArchived($applicationform)
            || !$this->permissions->allows(
                $user,
                'applicationforms.launch_validation',
                PermissionResolver::SCOPE_UNSCOPED,
            )
            || !$this->canViewHistorically($identity, $applicationform)
            || (
                $applicationform->get(Applicationform::FIELD_USER_ID) !== $user->get(User::FIELD_ID)
                && (int)$user->get(User::FIELD_ROLE_ID) !== User::ROLE_ADMIN
            )
        ) {
            return false;
        }

        if ($applicationform->has('validation_workflow_run')) {
            return $applicationform->validation_workflow_run === null;
        }

        return TableRegistry::getTableLocator()->get('ValidationWorkflowRuns')->find()
            ->where([
                ValidationWorkflowRun::FIELD_APPLICATIONFORM_ID => $applicationform->get(Applicationform::FIELD_ID),
            ])
            ->count() === 0;
    }

    /** Seul un administrateur visible peut effacer un cycle déjà lancé. */
    public function canResetValidation(IdentityInterface $identity, Applicationform $applicationform): bool
    {
        $user = $this->getValidUser($identity);
        if (
            $user === null
            || $this->isArchived($applicationform)
            || !$this->permissions->allows(
                $user,
                'applicationforms.reset_validation',
                PermissionResolver::SCOPE_UNSCOPED,
            )
            || !$this->canViewHistorically($identity, $applicationform)
            || (
                (int)$user->get(User::FIELD_ROLE_ID) !== User::ROLE_ADMIN
                && !$user->get(User::FIELD_ISSUPERUSER)
            )
        ) {
            return false;
        }

        return (new ApplicationformValidationWorkflow())->canReset($applicationform);
    }

    /** Seul un administrateur visible peut relancer un cycle bloqué. */
    public function canRemindValidation(IdentityInterface $identity, Applicationform $applicationform): bool
    {
        $user = $this->getValidUser($identity);

        return $user !== null
            && !$this->isArchived($applicationform)
            && $this->permissions->allows(
                $user,
                'applicationforms.remind_validation',
                PermissionResolver::SCOPE_UNSCOPED,
            )
            && $this->canViewHistorically($identity, $applicationform)
            && ($user->hasRole(User::ROLE_ADMIN) || $user->isSuperUser());
    }

    /** Seul un administrateur visible et habilité peut voter par procuration. */
    public function canOverrideValidation(IdentityInterface $identity, Applicationform $applicationform): bool
    {
        $user = $this->getValidUser($identity);

        return $user !== null
            && !$this->isArchived($applicationform)
            && $this->permissions->allows(
                $user,
                'applicationforms.override_validation',
                PermissionResolver::SCOPE_UNSCOPED,
            )
            && $this->canViewHistorically($identity, $applicationform)
            && ($user->hasRole(User::ROLE_ADMIN) || $user->isSuperUser());
    }

    /** Autorise l'archivage des demandes lisibles par un ADMIN ou un superuser. */
    public function canArchive(IdentityInterface $identity, Applicationform $applicationform): bool
    {
        $user = $this->getValidUser($identity);
        if (
            $user === null
            || !$this->permissions->allows($user, 'applicationforms.archive', PermissionResolver::SCOPE_UNSCOPED)
            || !$this->canView($identity, $applicationform)
            || $this->isArchived($applicationform)
            || $this->hasActiveWorkflow($applicationform)
        ) {
            return false;
        }

        return $user->isSuperUser() || $user->hasRole(User::ROLE_ADMIN);
    }

    /** Autorise la restauration d'une demande archivée et lisible. */
    public function canRestore(IdentityInterface $identity, Applicationform $applicationform): bool
    {
        $user = $this->getValidUser($identity);
        if (
            $user === null
            || !$this->permissions->allows($user, 'applicationforms.archive', PermissionResolver::SCOPE_UNSCOPED)
            || !$this->canView($identity, $applicationform)
            || !$this->isArchived($applicationform)
        ) {
            return false;
        }

        return $user->isSuperUser() || $user->hasRole(User::ROLE_ADMIN);
    }

    /** Autorise l'affichage des commandes d'archivage groupées de la grille. */
    public function canArchiveBulk(IdentityInterface $identity, Applicationform $applicationform): bool
    {
        $user = $this->getValidUser($identity);

        return $user !== null
            && $this->permissions->allows($user, 'applicationforms.archive', PermissionResolver::SCOPE_UNSCOPED)
            && ($user->isSuperUser() || $user->hasRole(User::ROLE_ADMIN));
    }

    /** Autorise l'affichage des commandes de restauration groupées de la grille. */
    public function canRestoreBulk(IdentityInterface $identity, Applicationform $applicationform): bool
    {
        return $this->canArchiveBulk($identity, $applicationform);
    }

    /** Le service réévalue ensuite le rôle courant, l'échéance et la suppléance. */
    public function canVoteValidation(IdentityInterface $identity, Applicationform $applicationform): bool
    {
        $user = $this->getValidUser($identity);

        return $user !== null
            && !$this->isArchived($applicationform)
            && $this->permissions->allows($user, 'applicationforms.vote', PermissionResolver::SCOPE_UNSCOPED)
            && $this->canViewHistorically($identity, $applicationform);
    }

    /** Indique si la DAE porte déjà un horodatage d'archivage. */
    private function isArchived(Applicationform $applicationform): bool
    {
        return $applicationform->get(Applicationform::FIELD_ARCHIVED) !== null;
    }

    /** Indique si un cycle en attente empêche l'archivage. */
    private function hasActiveWorkflow(Applicationform $applicationform): bool
    {
        return TableRegistry::getTableLocator()->get('ValidationWorkflowRuns')->find()
            ->where([
                ValidationWorkflowRun::FIELD_APPLICATIONFORM_ID => $applicationform->get(Applicationform::FIELD_ID),
                ValidationWorkflowRun::FIELD_STATE => ValidationWorkflowRun::STATE_PENDING,
            ])
            ->count() > 0;
    }

    /**
     * Autorisation pour la suppression (delete)
     *
     * @param \Authorization\IdentityInterface $identity
     * @param \App\Model\Entity\Applicationform $applicationform
     * @return bool
     */
    public function canDelete(IdentityInterface $identity, Applicationform $applicationform): bool
    {
        $user = $this->getValidUser($identity);
        if (!$user) {
            return false;
        }

        if ($user->get(User::FIELD_ISSUPERUSER)) {
            return true;
        }

        return $this->permissions->allowsApplicationform($user, 'applicationforms.delete', $applicationform)
            && !$this->hasWorkflow($applicationform)
            && $applicationform->get(Applicationform::FIELD_USER_ID) === $user->get(User::FIELD_ID);
    }

    // =========================================================================
    // REGLES D'ACCÈS AUX ZONES (VISIBILITÉ & ÉDITION)
    // =========================================================================

    // --- ZONE ADMIN ---

    /**
     * Autorisation pour la zone Admin view
     *
     * @param \Authorization\IdentityInterface $identity
     * @param \App\Model\Entity\Applicationform $applicationform
     * @return bool
     */
    public function canViewZoneAdmin(IdentityInterface $identity, Applicationform $applicationform): bool
    {
        $user = $this->getValidUser($identity);

        return $user !== null && ($applicationform->get(Applicationform::FIELD_ID) === null
            ? $this->canCreateApplicationform($user, $applicationform)
            : true);
    }

    /**
     * Autorisation pour la zone Admin edit
     *
     * @param \Authorization\IdentityInterface $identity
     * @param \App\Model\Entity\Applicationform $applicationform
     * @return bool
     */
    public function canEditZoneAdmin(IdentityInterface $identity, Applicationform $applicationform): bool
    {
        return $this->canEdit($identity, $applicationform);
    }

    // --- ZONE CONTRAT ---

    /**
     * Autorisation pour la zone Contrat view
     *
     * @param \Authorization\IdentityInterface $identity
     * @param \App\Model\Entity\Applicationform $applicationform
     * @return bool
     */
    public function canViewZoneContrat(IdentityInterface $identity, Applicationform $applicationform): bool
    {
        return $this->getValidUser($identity) !== null;
    }

    /**
     * Autorisation pour la zone Contrat edit
     *
     * @param \Authorization\IdentityInterface $identity
     * @param \App\Model\Entity\Applicationform $applicationform
     * @return bool
     */
    public function canEditZoneContrat(IdentityInterface $identity, Applicationform $applicationform): bool
    {
        return $this->canEdit($identity, $applicationform);
    }

    // --- ZONE RÉMUNÉRATION ---

    /**
     * Autorisation pour la zone rémunération view
     *
     * @param \Authorization\IdentityInterface $identity
     * @param \App\Model\Entity\Applicationform $applicationform
     * @return bool
     */
    public function canViewZoneRemuneration(IdentityInterface $identity, Applicationform $applicationform): bool
    {
        $user = $this->getValidUser($identity);
        if (!$user) {
            return false;
        }

        if ($applicationform->get(Applicationform::FIELD_ID) === null) {
            return $this->canCreateApplicationform($user, $applicationform);
        }

        return $user->get(User::FIELD_ISSUPERUSER)
            || $applicationform->get(Applicationform::FIELD_USER_ID) === $user->get(User::FIELD_ID)
            || in_array($user->get(User::FIELD_ROLE_ID), [
                User::ROLE_ADMIN,
                User::ROLE_2_VALIDEUR_RRH,
                User::ROLE_3_VALIDEUR_DRH,
            ]);
    }

    /**
     * Autorisation pour la zone rémunération edit
     *
     * @param \Authorization\IdentityInterface $identity
     * @param \App\Model\Entity\Applicationform $applicationform
     * @return bool
     */
    public function canEditZoneRemuneration(IdentityInterface $identity, Applicationform $applicationform): bool
    {
        $user = $this->getValidUser($identity);
        if (!$user) {
            return false;
        }

        if ($applicationform->get(Applicationform::FIELD_ID) === null) {
            return $this->canCreateApplicationform($user, $applicationform);
        }

        return $user->get(User::FIELD_ISSUPERUSER)
            || in_array($user->get(User::FIELD_ROLE_ID), [
                User::ROLE_ADMIN,
                User::ROLE_2_VALIDEUR_RRH,
                User::ROLE_3_VALIDEUR_DRH,
            ]);
    }

    // --- ZONE RÉSERVÉS (RH / ADMIN) ---

    /**
     * Autorisation pour la zone reserves view
     *
     * @param \Authorization\IdentityInterface $identity
     * @param \App\Model\Entity\Applicationform $applicationform
     * @return bool
     */
    public function canViewZoneReserves(IdentityInterface $identity, Applicationform $applicationform): bool
    {
        $user = $this->getValidUser($identity);
        if (!$user) {
            return false;
        }

        return $user->get(User::FIELD_ISSUPERUSER)
            || in_array($user->get(User::FIELD_ROLE_ID), [
                User::ROLE_ADMIN,
                User::ROLE_2_VALIDEUR_RRH,
                User::ROLE_3_VALIDEUR_DRH,
                User::ROLE_4_VALIDEUR_CG,
            ]);
    }

    /**
     * Autorisation pour la zone reserves edit
     *
     * @param \Authorization\IdentityInterface $identity
     * @param \App\Model\Entity\Applicationform $applicationform
     * @return bool
     */
    public function canEditZoneReserves(IdentityInterface $identity, Applicationform $applicationform): bool
    {
        return $this->canViewZoneReserves($identity, $applicationform);
    }

    // --- ZONE COMMENTAIRES ---

    /**
     * Autorisation pour la zone commentaires view
     *
     * @param \Authorization\IdentityInterface $identity
     * @param \App\Model\Entity\Applicationform $applicationform
     * @return bool
     */
    public function canViewZoneCommentaires(IdentityInterface $identity, Applicationform $applicationform): bool
    {
        return $this->getValidUser($identity) !== null;
    }

    /**
     * Autorisation pour la zone commentaires edit
     *
     * @param \Authorization\IdentityInterface $identity
     * @param \App\Model\Entity\Applicationform $applicationform
     * @return bool
     */
    public function canEditZoneCommentaires(IdentityInterface $identity, Applicationform $applicationform): bool
    {
        return $this->getValidUser($identity) !== null;
    }

    /** Vérifie les profils autorisés à initialiser une nouvelle demande. */
    private function canCreateApplicationform(User $user, Applicationform $applicationform): bool
    {
        if ($applicationform->get(Applicationform::FIELD_ID) !== null) {
            return false;
        }

        return $this->permissions->allows($user, 'applicationforms.create', PermissionResolver::SCOPE_SELF)
            && ($user->isSuperUser() || in_array($user->get(User::FIELD_ROLE_ID), [
                User::ROLE_DEMANDEUR,
                User::ROLE_ADMIN,
            ], true));
    }
}
