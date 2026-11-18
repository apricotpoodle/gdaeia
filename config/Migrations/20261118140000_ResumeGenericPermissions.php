<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Complète le catalogue des permissions et relie les destinations sûres des menus.
 */
final class ResumeGenericPermissions extends BaseMigration
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
    ];

    /** @var array<int, array{string, string, string}> */
    private const MENU_URL_PERMISSION_MATRIX = [
        ['applicationforms/add', 'applicationforms', 'create'],
        ['departments', 'departments', 'read'],
        ['departments/assignmanagers', 'departments', 'manage'],
        ['departments/assignservices', 'departments', 'manage'],
        ['menus', 'menus', 'manage'],
        ['menus/add', 'menus', 'manage'],
        ['menus/role_access', 'menus', 'manage'],
        ['users', 'users', 'read'],
        ['validationsequences', 'validationsequences', 'read'],
    ];

    public function up(): void
    {
        $this->addMenuPermissionReference();
        $this->insertMissingPermissions();
        $this->insertMissingPermissionScopes();
        $this->insertMissingRolePermissions();
        $this->reportMatrixExceptions();
        $this->linkUnambiguousMenuUrls();
        $this->reportMenusWithoutPermission();
    }

    public function down(): void
    {
        $menus = $this->table('menus');
        if (!$menus->hasColumn('required_permission_id')) {
            return;
        }

        if ($this->hasForeignKey('fk_menus_required_permission')) {
            $menus->dropForeignKey(
                'required_permission_id',
                'fk_menus_required_permission',
            );
        }
        if ($this->hasIndex('menus', 'idx_menus_required_permission_id')) {
            $menus->removeIndexByName('idx_menus_required_permission_id');
        }

        $menus->removeColumn('required_permission_id')->update();
    }

    private function addMenuPermissionReference(): void
    {
        $menus = $this->table('menus');
        $changed = false;

        if (!$menus->hasColumn('required_permission_id')) {
            $menus->addColumn('required_permission_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ]);
            $changed = true;
        }

        if (!$this->hasIndex('menus', 'idx_menus_required_permission_id')) {
            $menus->addIndex(['required_permission_id'], [
                'name' => 'idx_menus_required_permission_id',
            ]);
            $changed = true;
        }

        if (!$this->hasForeignKey('fk_menus_required_permission')) {
            $menus->addForeignKey(
                'required_permission_id',
                'permissions',
                'id',
                [
                    'constraint' => 'fk_menus_required_permission',
                    'delete' => 'RESTRICT',
                    'update' => 'CASCADE',
                ],
            );
            $changed = true;
        }

        if ($changed) {
            $menus->update();
        }
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

    private function linkUnambiguousMenuUrls(): void
    {
        $urlMap = self::selectRows(
            self::MENU_URL_PERMISSION_MATRIX,
            ['url_key', 'resource', 'action'],
        );
        $this->execute(
            "UPDATE menus menuRecord
             INNER JOIN ({$urlMap}) urlMap
                ON urlMap.url_key = LOWER(TRIM(BOTH '/' FROM TRIM(COALESCE(menuRecord.url, ''))))
             INNER JOIN permissions permissionRecord
                ON permissionRecord.resource = urlMap.resource
               AND permissionRecord.action = urlMap.action
             SET menuRecord.required_permission_id = permissionRecord.id
             WHERE menuRecord.required_permission_id IS NULL",
        );
    }

    private function reportMenusWithoutPermission(): void
    {
        $menus = $this->query(
            'SELECT id, url, name
             FROM menus
             WHERE required_permission_id IS NULL
             ORDER BY id',
        )->fetchAll('assoc');

        $io = $this->getIo();
        if ($io === null) {
            return;
        }

        if ($menus === []) {
            $io->out('Aucun menu ne reste sans permission requise.');

            return;
        }

        $io->out('Menus laissés sans permission requise (id, URL et nom) :');
        foreach ($menus as $menu) {
            $io->out('  ' . json_encode(
                $menu,
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

    private function hasIndex(string $table, string $index): bool
    {
        return $this->query(sprintf(
            "SHOW INDEX FROM `%s` WHERE Key_name = '%s'",
            $table,
            $index,
        ))->fetch() !== false;
    }

    private function hasForeignKey(string $constraint): bool
    {
        return $this->query(sprintf(
            "SELECT 1
             FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'menus'
               AND CONSTRAINT_TYPE = 'FOREIGN KEY'
               AND CONSTRAINT_NAME = '%s'
             LIMIT 1",
            $constraint,
        ))->fetch() !== false;
    }
}
