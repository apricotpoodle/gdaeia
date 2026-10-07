<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Applicationform;
use App\Model\Entity\User;
use App\Model\Entity\ValidationWorkflowRun;
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
    /**
     * Autorisation pour la liste (index)
     *
     * @param \Authorization\IdentityInterface $identity
     * @return bool
     */
    public function canIndex(IdentityInterface $identity): bool
    {
        $user = $this->getValidUser($identity);

        // Sécurité de base : il faut être connecté pour voir la page index.
        // Note : Le filtrage réel des données selon le périmètre de l'utilisateur
        // se fera plus tard au niveau de l'ORM dans l'API (via un custom finder).
        return $user !== null;
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
        if (!$user) {
            return false;
        }

        if ($user->get(User::FIELD_ISSUPERUSER) || $applicationform->get(Applicationform::FIELD_USER_ID) === $user->get(User::FIELD_ID)) {
            return true;
        }
        if ($applicationform->get(Applicationform::FIELD_DEPARTMENT_ID) === null) {
            return false;
        }

        return TableRegistry::getTableLocator()->get('UserDepartments')->find()
            ->where([
                User::FIELD_ID => $user->get(User::FIELD_ID),
                Applicationform::FIELD_DEPARTMENT_ID => $applicationform->get(Applicationform::FIELD_DEPARTMENT_ID),
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

        // Exemple : Tout profil autorisé à se connecter peut initier une demande
        return true;
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
        if (!$user) {
            return false;
        }

        if ($user->get(User::FIELD_ISSUPERUSER)) {
            return true;
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
            || !$this->canView($identity, $applicationform)
            || ($applicationform->get(Applicationform::FIELD_USER_ID) !== $user->get(User::FIELD_ID) && (int)$user->get(User::FIELD_ROLE_ID) !== User::ROLE_ADMIN)
        ) {
            return false;
        }

        if ($applicationform->has('validation_workflow_run')) {
            return $applicationform->validation_workflow_run === null;
        }

        return TableRegistry::getTableLocator()->get('ValidationWorkflowRuns')->find()
            ->where([ValidationWorkflowRun::FIELD_APPLICATIONFORM_ID => $applicationform->get(Applicationform::FIELD_ID)])
            ->count() === 0;
    }

    /** Seul un administrateur visible peut effacer un cycle déjà lancé. */
    public function canResetValidation(IdentityInterface $identity, Applicationform $applicationform): bool
    {
        $user = $this->getValidUser($identity);
        if (
            $user === null
            || !$this->canView($identity, $applicationform)
            || ((int)$user->get(User::FIELD_ROLE_ID) !== User::ROLE_ADMIN && !$user->get(User::FIELD_ISSUPERUSER))
        ) {
            return false;
        }

        return TableRegistry::getTableLocator()->get('ValidationWorkflowRuns')->find()
            ->where([ValidationWorkflowRun::FIELD_APPLICATIONFORM_ID => $applicationform->get(Applicationform::FIELD_ID)])
            ->count() === 1;
    }

    /** Le service réévalue ensuite le rôle courant, l'échéance et la suppléance. */
    public function canVoteValidation(IdentityInterface $identity, Applicationform $applicationform): bool
    {
        return $this->canView($identity, $applicationform);
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

        return !$this->hasWorkflow($applicationform)
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
        return $this->getValidUser($identity) !== null;
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

        // Exemple : Accessible aux Admins, RH et Créateurs
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
}
