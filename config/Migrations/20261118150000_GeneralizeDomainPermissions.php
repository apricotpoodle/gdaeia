<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/** Complète les permissions métier et d’administration sans modifier les données existantes. */
final class GeneralizeDomainPermissions extends BaseMigration
{
    /** @var array<int, array{string, string, string}> */
    private const PERMISSIONS = [
        ['applicationforms', 'create', 'Créer une demande'],
        ['applicationforms', 'read', 'Consulter les demandes'],
        ['applicationforms', 'update', 'Modifier les demandes'],
        ['applicationforms', 'delete', 'Supprimer les demandes'],
        ['applicationforms', 'vote', 'Voter sur les demandes'],
        ['users', 'read', 'Consulter les utilisateurs'],
        ['users', 'update', 'Modifier les utilisateurs'],
        ['departments', 'read', 'Consulter les départements'],
        ['departments', 'manage', 'Administrer les départements'],
        ['validationsequences', 'read', 'Consulter les séquences de validation'],
        ['menus', 'manage', 'Administrer les menus'],
        ['users', 'create', 'Créer les utilisateurs'],
        ['users', 'delete', 'Supprimer les utilisateurs'],
        ['users', 'change_role', 'Changer les rôles des utilisateurs'],
        ['users', 'associate_departments', 'Associer les départements des utilisateurs'],
        ['users', 'impersonate', 'Usurper une identité'],
        ['applicationforms', 'launch_validation', 'Lancer la validation des demandes'],
        ['applicationforms', 'reset_validation', 'Réinitialiser la validation des demandes'],
        ['applicationforms', 'remind_validation', 'Relancer la validation des demandes'],
        ['applicationforms', 'override_validation', 'Outrepasser la validation des demandes'],
        ['applicationforms', 'archive', 'Archiver les demandes'],
        ['references', 'read', 'Consulter les références'],
        ['references', 'create', 'Créer les références'],
        ['references', 'update', 'Modifier les références'],
        ['references', 'delete', 'Supprimer les références'],
        ['roles', 'read', 'Consulter les rôles'],
        ['roles', 'create', 'Créer les rôles'],
        ['roles', 'update', 'Modifier les rôles'],
        ['roles', 'delete', 'Supprimer les rôles'],
        ['fieldauthorizations', 'read', 'Consulter les autorisations de champs'],
        ['fieldauthorizations', 'create', 'Créer les autorisations de champs'],
        ['fieldauthorizations', 'update', 'Modifier les autorisations de champs'],
        ['fieldauthorizations', 'delete', 'Supprimer les autorisations de champs'],
        ['workflowsettings', 'read', 'Consulter le paramétrage global du workflow'],
        ['workflowsettings', 'update', 'Modifier le paramétrage global du workflow'],
        ['validationcommenttemplates', 'read', 'Consulter les commentaires prédéfinis'],
        ['validationcommenttemplates', 'create', 'Créer les commentaires prédéfinis'],
        ['validationcommenttemplates', 'update', 'Modifier les commentaires prédéfinis'],
        ['validationcommenttemplates', 'delete', 'Supprimer les commentaires prédéfinis'],
        ['validationsequences', 'create', 'Créer les séquences de validation'],
        ['validationsequences', 'update', 'Modifier les séquences de validation'],
        ['validationsequences', 'delete', 'Supprimer les séquences de validation'],
    ];

    /** @var array<int, array{string, string, string}> */
    private const PERMISSION_SCOPES = [
        ['applicationforms', 'create', 'self'],
        ['applicationforms', 'read', 'self'],
        ['applicationforms', 'read', 'assigned'],
        ['applicationforms', 'read', 'all'],
        ['applicationforms', 'update', 'self'],
        ['applicationforms', 'update', 'assigned'],
        ['applicationforms', 'update', 'all'],
        ['applicationforms', 'delete', 'self'],
        ['applicationforms', 'vote', 'unscoped'],
        ['users', 'read', 'self'],
        ['users', 'read', 'assigned'],
        ['users', 'read', 'all'],
        ['users', 'update', 'self'],
        ['users', 'update', 'assigned'],
        ['departments', 'read', 'assigned'],
        ['departments', 'read', 'all'],
        ['departments', 'manage', 'assigned'],
        ['departments', 'manage', 'all'],
        ['validationsequences', 'read', 'assigned'],
        ['validationsequences', 'read', 'all'],
        ['menus', 'manage', 'unscoped'],
        ['users', 'create', 'unscoped'],
        ['users', 'delete', 'unscoped'],
        ['users', 'change_role', 'unscoped'],
        ['users', 'associate_departments', 'assigned'],
        ['users', 'impersonate', 'unscoped'],
        ['applicationforms', 'launch_validation', 'unscoped'],
        ['applicationforms', 'reset_validation', 'unscoped'],
        ['applicationforms', 'remind_validation', 'unscoped'],
        ['applicationforms', 'override_validation', 'unscoped'],
        ['applicationforms', 'archive', 'unscoped'],
        ['references', 'read', 'unscoped'],
        ['references', 'create', 'unscoped'],
        ['references', 'update', 'unscoped'],
        ['references', 'delete', 'unscoped'],
        ['roles', 'read', 'unscoped'],
        ['roles', 'create', 'unscoped'],
        ['roles', 'update', 'unscoped'],
        ['roles', 'delete', 'unscoped'],
        ['fieldauthorizations', 'read', 'unscoped'],
        ['fieldauthorizations', 'create', 'unscoped'],
        ['fieldauthorizations', 'update', 'unscoped'],
        ['fieldauthorizations', 'delete', 'unscoped'],
        ['workflowsettings', 'read', 'unscoped'],
        ['workflowsettings', 'update', 'unscoped'],
        ['validationcommenttemplates', 'read', 'unscoped'],
        ['validationcommenttemplates', 'create', 'unscoped'],
        ['validationcommenttemplates', 'update', 'unscoped'],
        ['validationcommenttemplates', 'delete', 'unscoped'],
        ['validationsequences', 'create', 'assigned'],
        ['validationsequences', 'update', 'assigned'],
        ['validationsequences', 'delete', 'assigned'],
    ];

    /** @var array<int, array{string, string, array<int, int>, array<int, string>}> */
    private const ROLE_PERMISSION_MATRIX = [
        ['applicationforms', 'create', [1, 2], ['self']],
        ['applicationforms', 'read', [1, 3, 4, 5, 6, 7], ['self', 'assigned']],
        ['applicationforms', 'read', [2], ['self']],
        ['applicationforms', 'update', [1, 3, 4, 5, 6, 7], ['self', 'assigned']],
        ['applicationforms', 'update', [2], ['self']],
        ['applicationforms', 'delete', [1, 2, 3, 4, 5, 6, 7], ['self']],
        ['applicationforms', 'vote', [1, 2, 3, 4, 5, 6, 7], ['unscoped']],
        ['users', 'read', [1, 2, 3, 4, 5, 6, 7], ['self']],
        ['users', 'read', [1], ['assigned']],
        ['users', 'update', [1, 2, 3, 4, 5, 6, 7], ['self']],
        ['users', 'update', [1], ['assigned']],
        ['departments', 'read', [1], ['assigned']],
        ['departments', 'manage', [1], ['assigned']],
        ['users', 'create', [1], ['unscoped']],
        ['users', 'delete', [1], ['unscoped']],
        ['users', 'change_role', [1], ['unscoped']],
        ['users', 'associate_departments', [1], ['assigned']],
        ['applicationforms', 'launch_validation', [1, 2, 3, 4, 5, 6, 7], ['unscoped']],
        ['applicationforms', 'reset_validation', [1], ['unscoped']],
        ['applicationforms', 'remind_validation', [1], ['unscoped']],
        ['applicationforms', 'override_validation', [1], ['unscoped']],
        ['applicationforms', 'archive', [1, 7], ['unscoped']],
    ];

    public function up(): void
    {
        $this->insertMissingPermissions();
        $this->insertMissingPermissionScopes();
        $this->insertMissingRolePermissions();
        $this->reportMatrixExceptions();
    }

    /** Les attributions préexistantes et les migrations antérieures restent intactes. */
    public function down(): void
    {
        $this->getIo()?->out('Permissions, périmètres et attributions conservés ; aucune donnée supprimée.');
    }

    private function insertMissingPermissions(): void
    {
        $permissions = self::selectRows(self::PERMISSIONS, ['resource', 'action', 'label']);
        $this->execute(
            "INSERT INTO permissions (resource, action, label)
             SELECT expectedRows.resource, expectedRows.action, expectedRows.label
             FROM ({$permissions}) expectedRows
             WHERE NOT EXISTS (
                SELECT 1
                FROM permissions existingPermission
                WHERE existingPermission.resource = expectedRows.resource
                  AND existingPermission.action = expectedRows.action
             )",
        );
    }

    private function insertMissingPermissionScopes(): void
    {
        $scopes = self::selectRows(
            self::PERMISSION_SCOPES,
            ['resource', 'action', 'scope_type'],
        );
        $this->execute(
            "INSERT INTO permission_scopes (permission_id, scope_type)
             SELECT permissionRecord.id, expectedRows.scope_type
             FROM ({$scopes}) expectedRows
             INNER JOIN permissions permissionRecord
                ON permissionRecord.resource = expectedRows.resource
               AND permissionRecord.action = expectedRows.action
             WHERE NOT EXISTS (
                SELECT 1
                FROM permission_scopes existingScope
                WHERE existingScope.permission_id = permissionRecord.id
                  AND existingScope.scope_type = expectedRows.scope_type
             )",
        );
    }

    private function insertMissingRolePermissions(): void
    {
        $assignments = self::selectRows(
            self::rolePermissionRows(),
            ['role_id', 'resource', 'action', 'scope_type'],
        );
        $this->execute(
            "INSERT INTO role_permissions (role_id, permission_id, scope_type)
             SELECT expectedRows.role_id, permissionRecord.id, expectedRows.scope_type
             FROM ({$assignments}) expectedRows
             INNER JOIN roles roleRecord ON roleRecord.id = expectedRows.role_id
             INNER JOIN permissions permissionRecord
                ON permissionRecord.resource = expectedRows.resource
               AND permissionRecord.action = expectedRows.action
             WHERE NOT EXISTS (
                SELECT 1
                FROM role_permissions existingAssignment
                WHERE existingAssignment.role_id = expectedRows.role_id
                  AND existingAssignment.permission_id = permissionRecord.id
                  AND existingAssignment.scope_type = expectedRows.scope_type
             )",
        );
    }

    private function reportMatrixExceptions(): void
    {
        $permissions = self::selectRows(self::PERMISSIONS, ['resource', 'action', 'label']);
        $scopes = self::selectRows(
            self::PERMISSION_SCOPES,
            ['resource', 'action', 'scope_type'],
        );
        $assignments = self::selectRows(
            self::rolePermissionRows(),
            ['role_id', 'resource', 'action', 'scope_type'],
        );
        $exceptions = $this->query(
            "SELECT 'permission_hors_matrice' AS exception_type,
                    permissionRecord.code, NULL AS role_id, NULL AS scope_type,
                    permissionRecord.label AS existing_value
             FROM permissions permissionRecord
             LEFT JOIN ({$permissions}) expectedPermission
                ON expectedPermission.resource = permissionRecord.resource
               AND expectedPermission.action = permissionRecord.action
             WHERE expectedPermission.resource IS NULL
             UNION ALL
             SELECT 'libelle_different', permissionRecord.code, NULL, NULL,
                    permissionRecord.label
             FROM permissions permissionRecord
             INNER JOIN ({$permissions}) expectedPermission
                ON expectedPermission.resource = permissionRecord.resource
               AND expectedPermission.action = permissionRecord.action
             WHERE permissionRecord.label <> expectedPermission.label
             UNION ALL
             SELECT 'perimetre_hors_matrice', permissionRecord.code, NULL,
                    permissionScope.scope_type, NULL
             FROM permission_scopes permissionScope
             INNER JOIN permissions permissionRecord
                ON permissionRecord.id = permissionScope.permission_id
             LEFT JOIN ({$scopes}) expectedScope
                ON expectedScope.resource = permissionRecord.resource
               AND expectedScope.action = permissionRecord.action
               AND expectedScope.scope_type = permissionScope.scope_type
             WHERE expectedScope.resource IS NULL
             UNION ALL
             SELECT 'attribution_hors_matrice', permissionRecord.code,
                    rolePermission.role_id, rolePermission.scope_type, NULL
             FROM role_permissions rolePermission
             INNER JOIN permissions permissionRecord
                ON permissionRecord.id = rolePermission.permission_id
             LEFT JOIN ({$assignments}) expectedAssignment
                ON expectedAssignment.role_id = rolePermission.role_id
               AND expectedAssignment.resource = permissionRecord.resource
               AND expectedAssignment.action = permissionRecord.action
               AND expectedAssignment.scope_type = rolePermission.scope_type
             WHERE expectedAssignment.role_id IS NULL",
        )->fetchAll('assoc');

        $io = $this->getIo();
        if ($io === null) {
            return;
        }

        if ($exceptions === []) {
            $io->out('Aucune exception préexistante à la matrice des permissions.');

            return;
        }

        $io->out('Exceptions préexistantes conservées par rapport à la matrice :');
        foreach ($exceptions as $exception) {
            $io->out('  ' . json_encode(
                $exception,
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
            ));
        }
    }

    /**
     * @return array<int, array{int, string, string, string}>
     */
    private static function rolePermissionRows(): array
    {
        $rows = [];
        foreach (self::ROLE_PERMISSION_MATRIX as [$resource, $action, $roleIds, $scopes]) {
            foreach ($roleIds as $roleId) {
                foreach ($scopes as $scope) {
                    $rows[] = [$roleId, $resource, $action, $scope];
                }
            }
        }

        return $rows;
    }

    /**
     * @param array<int, array<int, int|string>> $rows
     * @param array<int, string> $columns
     */
    private static function selectRows(array $rows, array $columns): string
    {
        $selects = [];
        foreach ($rows as $row) {
            $fields = [];
            foreach ($columns as $index => $column) {
                $value = $row[$index];
                $literal = is_int($value)
                    ? (string)$value
                    : "'" . str_replace("'", "''", $value) . "'";
                $fields[] = $literal . ' AS `' . $column . '`';
            }
            $selects[] = 'SELECT ' . implode(', ', $fields);
        }

        return implode("\nUNION ALL\n", $selects);
    }

}
