<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Applicationform;
use App\Model\Entity\User;
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

        if ($user->get('issuperuser') || $applicationform->user_id === $user->id) {
            return true;
        }
        if ($applicationform->department_id === null) {
            return false;
        }

        return TableRegistry::getTableLocator()->get('UserDepartments')->find()
            ->where(['user_id' => $user->id, 'department_id' => $applicationform->department_id])
            ->count() > 0;
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

        if ((int)$user->get('role_id') === User::ROLE_ADMIN || $user->get('issuperuser')) {
            return true;
        }
        if (!$this->canView($identity, $applicationform)) {
            return false;
        }
        return $applicationform->user_id === $user->id || in_array($user->get('role_id'), Applicationform::ALLOWED_ROLES_FOR_EDIT);
    }

    /** Le créateur ou un Admin visible peut soumettre une AF au cycle. */
    public function canLaunchValidation(IdentityInterface $identity, Applicationform $applicationform): bool
    {
        $user = $this->getValidUser($identity);
        if (
            $user === null
            || !$this->canView($identity, $applicationform)
            || ($applicationform->user_id !== $user->id && (int)$user->role_id !== User::ROLE_ADMIN)
        ) {
            return false;
        }

        if ($applicationform->has('validation_workflow_run')) {
            return $applicationform->validation_workflow_run === null;
        }

        return TableRegistry::getTableLocator()->get('ValidationWorkflowRuns')->find()
            ->where(['applicationform_id' => $applicationform->id])
            ->count() === 0;
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

        // Règle restrictive : Super Admin ou propriétaire de la demande
        return $user->get('issuperuser') || $applicationform->user_id === $user->id;
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
        return $user->get('issuperuser')
            || $applicationform->user_id === $user->id
            || in_array($user->get('role_id'), [User::ROLE_ADMIN, User::ROLE_2_VALIDEUR_RRH, User::ROLE_3_VALIDEUR_DRH]);
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

        return $user->get('issuperuser')
            || in_array($user->get('role_id'), [User::ROLE_ADMIN, User::ROLE_2_VALIDEUR_RRH, User::ROLE_3_VALIDEUR_DRH]);
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

        return $user->get('issuperuser')
            || in_array($user->get('role_id'), [User::ROLE_ADMIN, User::ROLE_2_VALIDEUR_RRH, User::ROLE_3_VALIDEUR_DRH, User::ROLE_4_VALIDEUR_CG]);
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
