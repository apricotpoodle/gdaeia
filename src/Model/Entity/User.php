<?php

declare(strict_types=1);

namespace App\Model\Entity;

use Authorization\AuthorizationServiceInterface;
use Authorization\Policy\ResultInterface;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use Authentication\IdentityInterface as AuthenticationIdentity;
use Authorization\IdentityInterface as AuthorizationIdentity;

/**
 * Class User
 * @package App\Model\Entity
 */
class User extends AppEntity implements AuthenticationIdentity, AuthorizationIdentity
{
    /**
     * 🆔 CARTOGRAPHIE DES RÔLES CRITIQUES (IDs de la base de données)
     * Centraliser ces IDs ici évite les nombres magiques dans toute l'application.
     */
    public const ROLE_ADMIN                     = 1;
    public const ROLE_DEMANDEUR                 = 2;
    public const ROLE_1_VALIDEUR_POLE           = 3;
    public const ROLE_2_VALIDEUR_RRH            = 4;
    public const ROLE_3_VALIDEUR_DRH            = 5;
    public const ROLE_4_VALIDEUR_CG             = 6;
    public const ROLE_5_VALIDEUR_DIR            = 7;



    /**
     * Liste des IDs de rôles (ex: Staff, RH) autorisés à créer d'autres utilisateurs
     * en dehors des Super Administrateurs.
     */
    public const ALLOWED_ROLES_FOR_CREATE = [self::ROLE_ADMIN];



    protected array $_accessible = [
        '*' => true,
        'id' => false,
    ];

    // AJOUTEZ CECI POUR SÉCURISER L'API :
    protected array $_hidden = [
        'password',
        'token'
    ];


    public function getIdentifier(): int|string|null
    {
        return $this->id;
    }

    public function can(string $action, mixed $resource): bool
    {
        return $this->authorization->can($this, $action, $resource);
    }

    public function canResult(string $action, mixed $resource): ResultInterface
    {
        return $this->authorization->canResult($this, $action, $resource);
    }

    public function applyScope(string $action, mixed $resource, mixed ...$optionalArgs): mixed
    {
        return $this->authorization->applyScope($this, $action, $resource, ...$optionalArgs);
    }

    public function getOriginalData(): \ArrayAccess|array
    {
        return $this;
    }

    public function setAuthorization(AuthorizationServiceInterface $service): static
    {
        $this->authorization = $service;

        return $this;
    }


    // /**
    //  * Application du scénario de test sur les boutons.
    //  * @return array<string, bool>
    //  */
    // protected function getActionPermissions(): array
    // {

    //     return [
    //         'view' => true,
    //         'edit' => ($this->id % 2 === 0), // Autorisé uniquement pour les IDs pairs
    //         'delete' => ($this->id % 2 !== 0), // Désactivé pour tout le monde
    //         'impersonate' => ($this->id % 2 === 0), // Autorisé uniquement pour les IDs pairs
    //     ];
    // }

    // /**
    //  * Application du scénario de test sur les colonnes.
    //  * @return array<string, bool>
    //  */
    // protected function getColumnVisibility(): array
    // {
    //     return [
    //         'email' => true,
    //         'issuperuser' => ($this->id % 2 == 0), // On force le masquage de la colonne Super Admin
    //     ];
    // }

    /**
     * automatically hash passwords when users update their password
     *
     * @param string $password -
     * @return string
     */
    protected function _setPassword(string $password): string
    {
        $hasher = new DefaultPasswordHasher();

        return $hasher->hash($password);
    }
}
