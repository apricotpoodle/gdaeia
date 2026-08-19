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
