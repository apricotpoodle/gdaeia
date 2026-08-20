<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Applicationform;
use App\Model\Entity\User;
use Authorization\IdentityInterface;

/**
 * Class ApplicationformPolicy
 *
 * Politiques d'accès pour les demandes de recrutement (Applicationforms).
 */
class ApplicationformPolicy
{
    /**
     * Méthode utilitaire DRY : Extrait et garantit le type de l'identité connectée.
     * Si l'identité n'est pas un humain (ex: un démon système ou une API), renvoie null.
     */
    private function getValidUser(IdentityInterface $identity): ?User
    {
        $user = $identity->getOriginalData();

        return $user instanceof User ? $user : null;
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

        // Le Super Admin a toujours accès, sinon à affiner selon les règles métier
        return true;
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

        // Le Super Admin ou le créateur de la demande
        return $user->get('issuperuser') || $applicationform->user_id === $user->id || in_array($user->get('role_id'), Applicationform::ALLOWED_ROLES_FOR_EDIT);
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
