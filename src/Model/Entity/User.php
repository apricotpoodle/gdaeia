<?php
declare(strict_types=1);

namespace App\Model\Entity;

use ArrayAccess;
use Authentication\IdentityInterface as AuthenticationIdentity;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use Authorization\AuthorizationServiceInterface;
use Authorization\IdentityInterface as AuthorizationIdentity;
use Authorization\Policy\ResultInterface;

/**
 * Class User
 *
 * @property int $id
 * @property string|null $firstname
 * @property string|null $lastname
 * @property string $email
 * @property string|null $username
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property-read string $full_name  Nom complet (Prénom Nom)
 * @property-read string $display_name  Nom d'affichage par défaut avec fallback
 * @package App\Model\Entity
 */
class User extends AppEntity implements AuthenticationIdentity, AuthorizationIdentity
{
    /**
     * Liste des IDs de rôles (ex: Staff, RH) autorisés à créer d'autres utilisateurs
     * en dehors des Super Administrateurs.
     */
    public const ALLOWED_ROLES_FOR_CREATE = [self::ROLE_ADMIN];
    public const ALLOWED_ROLES_FOR_EDIT   = [self::ROLE_ADMIN];
    public const ALLOWED_ROLES_FOR_VIEW   = [self::ROLE_ADMIN];
    public const ALLOWED_ROLES_FOR_DELETE = [self::ROLE_ADMIN];

    protected array $_accessible = [
        '*' => true,
        'id' => false,
        'user_departments' => true,
    ];

    // AJOUTEZ CECI POUR SÉCURISER L'API :
    protected array $_hidden = [
        'password',
        'token',
    ];

    /**
     * Liste des champs virtuels à exposer automatiquement lors de la conversion en tableau / JSON.
     *
     * @var array<int, string>
     */
    protected array $_virtual = [
        'full_name',
        'display_name',
    ];

    /**
     * Accesseur virtuel : Nom complet (Prénom + Nom)
     * Exemple : "Jean Dupont"
     *
     * @return string
     */
    protected function _getFullName(): string
    {
        $firstname = trim($this->firstname ?? '');
        $lastname = trim($this->lastname ?? '');

        $name = trim($firstname . ' ' . $lastname);

        return $name;
    }

    /**
     * Vérifie si l'utilisateur possède un rôle spécifique ou s'il appartient à un ensemble de rôles autorisés.
     *
     * @param int|int[] $roleAllowed ID de rôle unique ou tableau d'IDs de rôles autorisés.
     * @return bool Vrai si l'utilisateur possède l'un des rôles spécifiés.
     */
    public function hasRole(int|array $roleAllowed): bool
    {
        if (!isset($this->role_id)) {
            return false;
        }

        if (is_array($roleAllowed)) {
            return in_array($this->role_id, $roleAllowed, true);
        }

        return $this->role_id === $roleAllowed;
    }

    /**
     * Accesseur virtuel : Nom d'affichage intelligent (Display Name)
     * Utilise le nom complet si disponible, sinon le nom d'utilisateur, sinon l'adresse email.
     *
     * @return string
     */
    protected function _getDisplayName(): string
    {
        $fullName = $this->full_name;

        if (!empty($fullName)) {
            return $fullName;
        }

        if (!empty($this->username)) {
            return trim($this->username);
        }

        return $this->email ?? '';
    }

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

    public function getOriginalData(): ArrayAccess|array
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
