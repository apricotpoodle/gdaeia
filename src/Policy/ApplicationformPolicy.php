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
     * @param \App\Model\Entity\Applicationform|null $applicationform (L'entité vide passée par le contrôleur)
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
        if (!$user) return false;

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
        if (!$user) return false;

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
        if (!$user) return false;

        // Le Super Admin ou le créateur de la demande
        return $user->get('issuperuser') || $applicationform->user_id === $user->id;
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
        if (!$user) return false;

        // Règle restrictive : Super Admin ou propriétaire de la demande
        return $user->get('issuperuser') || $applicationform->user_id === $user->id;
    }
}
