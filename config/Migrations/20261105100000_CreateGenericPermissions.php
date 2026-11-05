<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/** Crée le catalogue générique des permissions et reprend l’administration des départements. */
final class CreateGenericPermissions extends BaseMigration
{
    public function up(): void
    {
        $this->execute(
            "CREATE TABLE permissions (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                resource VARCHAR(64) NOT NULL COMMENT 'Ressource protégée',
                action VARCHAR(64) NOT NULL COMMENT 'Capacité sur la ressource',
                code VARCHAR(129) GENERATED ALWAYS AS (CONCAT(resource, '.', action)) STORED,
                label VARCHAR(255) NOT NULL COMMENT 'Libellé français',
                created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                modified TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_permissions_resource_action (resource, action),
                UNIQUE KEY uq_permissions_code (code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
              COMMENT='Catalogue des capacités applicatives'",
        );

        $this->execute(
            "CREATE TABLE permission_scopes (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                permission_id INT UNSIGNED NOT NULL,
                scope_type ENUM('self', 'assigned', 'all', 'unscoped') NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_permission_scopes_permission_scope (permission_id, scope_type),
                CONSTRAINT fk_permission_scopes_permission FOREIGN KEY (permission_id)
                    REFERENCES permissions (id) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
              COMMENT='Périmètres admissibles par permission'",
        );

        $this->execute(
            "CREATE TABLE role_permissions (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                role_id INT UNSIGNED NOT NULL,
                permission_id INT UNSIGNED NOT NULL,
                scope_type ENUM('self', 'assigned', 'all', 'unscoped') NOT NULL,
                created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                modified TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_role_permissions_role_permission_scope (role_id, permission_id, scope_type),
                KEY idx_role_permissions_permission (permission_id),
                CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id)
                    REFERENCES roles (id) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id)
                    REFERENCES permissions (id) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
              COMMENT='Attributions explicites de permissions aux rôles'",
        );

        $this->execute(
            "INSERT INTO permissions (resource, action, label)
             VALUES
                ('departments', 'read', 'Consulter les départements'),
                ('departments', 'manage', 'Administrer les départements'),
                ('menus', 'manage', 'Administrer les menus')",
        );
        $this->execute(
            "INSERT INTO permission_scopes (permission_id, scope_type)
             SELECT id, scope_type
             FROM permissions
             CROSS JOIN (
                SELECT 'assigned' AS scope_type
                UNION ALL SELECT 'all'
             ) scopes
             WHERE resource = 'departments'",
        );
        $this->execute(
            "INSERT INTO permission_scopes (permission_id, scope_type)
             SELECT id, 'unscoped'
             FROM permissions
             WHERE resource = 'menus'",
        );
        $this->execute(
            "INSERT INTO role_permissions (role_id, permission_id, scope_type)
             SELECT roles.id, permissions.id, scope_type
             FROM permissions
             INNER JOIN roles ON roles.id = 1
             CROSS JOIN (
                SELECT 'assigned' AS scope_type
             ) scopes
             WHERE resource = 'departments'",
        );
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS role_permissions');
        $this->execute('DROP TABLE IF EXISTS permission_scopes');
        $this->execute('DROP TABLE IF EXISTS permissions');
    }
}
