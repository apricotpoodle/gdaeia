<?php
declare(strict_types=1);

namespace App\Service\Security;

use App\Model\Entity\User;
use Cake\ORM\TableRegistry;

/** Résout les permissions effectives d’un utilisateur avec refus par défaut. */
final class PermissionResolver
{
    public const SCOPE_SELF = 'self';
    public const SCOPE_ASSIGNED = 'assigned';
    public const SCOPE_ALL = 'all';
    public const SCOPE_UNSCOPED = 'unscoped';
    public const SCOPE_TYPES = [self::SCOPE_SELF, self::SCOPE_ASSIGNED, self::SCOPE_ALL, self::SCOPE_UNSCOPED];

    /** @param array<string, list<string>>|null $grants Null utilise les attributions persistées. */
    public function __construct(private readonly ?array $grants = null)
    {
    }

    /** Indique si l’utilisateur possède une permission, éventuellement sur un périmètre. */
    public function allows(User $user, string $code, ?string $scope = null): bool
    {
        if ($user->isSuperUser()) {
            return true;
        }

        if ($this->grants !== null) {
            $scopes = $this->grants[$code] ?? [];

            return $scope === null ? $scopes !== [] : in_array($scope, $scopes, true);
        }

        $roleId = $user->get('role_id');
        if (!is_int($roleId) && !ctype_digit((string)$roleId)) {
            return false;
        }

        /** @var \App\Model\Table\RolePermissionsTable $table */
        $table = TableRegistry::getTableLocator()->get('RolePermissions');
        $rows = $table->find('forRolePermission', roleId: (int)$roleId, code: $code)->all();
        foreach ($rows as $row) {
            if ($scope === null || $row->get('scope_type') === $scope) {
                return true;
            }
        }

        return false;
    }

    /** Vérifie le périmètre organisationnel d’une permission départementale. */
    public function allowsDepartment(User $user, string $code, ?int $departmentId = null): bool
    {
        if ($user->isSuperUser()) {
            return true;
        }

        if ($this->hasConfiguredScope($user, $code, self::SCOPE_ALL)) {
            return true;
        }

        if (!$this->hasConfiguredScope($user, $code, self::SCOPE_ASSIGNED)) {
            return false;
        }

        $table = TableRegistry::getTableLocator()->get('UserDepartments');
        $query = $table->find()->where(['user_id' => $user->get('id')]);
        if ($departmentId !== null) {
            $query->where(['department_id' => $departmentId]);
        }

        return $query->count() > 0;
    }

    /** Vérifie un périmètre depuis les données injectées ou persistées. */
    private function hasConfiguredScope(User $user, string $code, string $scope): bool
    {
        if ($this->grants !== null) {
            return in_array($scope, $this->grants[$code] ?? [], true);
        }

        return $this->allows($user, $code, $scope);
    }
}
