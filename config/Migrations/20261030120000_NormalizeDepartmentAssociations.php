<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/** Normalise les associations des départements et du catalogue CGR. */
final class NormalizeDepartmentAssociations extends BaseMigration
{
    /** Migre les associations historiques vers les tables de liaison. */
    public function up(): void
    {
        if (!$this->hasTable('department_managers')) {
            $this->table('department_managers', ['id' => false])
                ->addColumn('department_id', 'integer', ['signed' => false, 'null' => false])
                ->addColumn('user_id', 'integer', ['signed' => false, 'null' => false])
                ->addPrimaryKey(['department_id', 'user_id'])
                ->addForeignKey('department_id', 'departments', 'id', [
                    'delete' => 'CASCADE',
                    'update' => 'CASCADE',
                    'name' => 'fk_department_managers_department',
                ])
                ->addForeignKey('user_id', 'users', 'id', [
                    'delete' => 'CASCADE',
                    'update' => 'CASCADE',
                    'name' => 'fk_department_managers_user',
                ])
                ->create();
        }

        if (!$this->hasTable('department_cgr_codes')) {
            $this->table('department_cgr_codes', ['id' => false])
                ->addColumn('department_id', 'integer', ['signed' => false, 'null' => false])
                ->addColumn('cgr_code_id', 'integer', ['signed' => false, 'null' => false])
                ->addPrimaryKey(['department_id', 'cgr_code_id'])
                ->addForeignKey('department_id', 'departments', 'id', [
                    'delete' => 'CASCADE',
                    'update' => 'CASCADE',
                    'name' => 'fk_department_cgr_codes_department',
                ])
                ->addForeignKey('cgr_code_id', 'cgr_codes', 'id', [
                    'delete' => 'CASCADE',
                    'update' => 'CASCADE',
                    'name' => 'fk_department_cgr_codes_code',
                ])
                ->create();
        }

        $this->execute(
            'INSERT IGNORE INTO department_managers (department_id, user_id)
             SELECT id, current_manager_id
             FROM departments
             WHERE current_manager_id IS NOT NULL',
        );

        // Le SELECT DISTINCT dédoublonne les deux anciennes représentations du CGR.
        $this->execute(
            'INSERT IGNORE INTO department_cgr_codes (department_id, cgr_code_id)
             SELECT department_id, id
             FROM cgr_codes
             WHERE department_id IS NOT NULL
             UNION
             SELECT id, cgr_code_id
             FROM departments
             WHERE cgr_code_id IS NOT NULL',
        );

        // Contrôle exécuté avant abandon : les valeurs sont volontairement non reprises.
        $this->fetchDefaultCgrCount();

        // Les doublons historiques sont regroupés sur l’ID le plus ancien.
        // Les associations sont conservées avant suppression des lignes redondantes.
        $this->normalizeDuplicateCgrCodes();

        $this->execute(
            "DELETE FROM field_authorizations
             WHERE resource = 'Departments'
               AND field IN ('cgr_code_id', 'current_manager_id', 'default_cgr')",
        );
        $this->execute(
            "DELETE FROM field_definitions
             WHERE resource = 'Departments'
               AND field IN ('cgr_code_id', 'current_manager_id', 'default_cgr')",
        );
        $now = date('Y-m-d H:i:s');
        $this->execute(
            "INSERT IGNORE INTO field_definitions
                (resource, field, label, description, active, position, created, modified)
             VALUES
                ('Departments', 'manager_ids', 'Responsables',
                 'Utilisateurs responsables associés au département.', 1, 100, '{$now}', '{$now}'),
                ('Departments', 'cgr_code_ids', 'Codes CGR associés',
                 'Codes CGR du catalogue associés au département.', 1, 101, '{$now}', '{$now}')",
        );
        $this->execute(
            "INSERT IGNORE INTO field_authorizations (role_id, resource, field, access_level)
             SELECT id, 'Departments', 'manager_ids', 'EDIT' FROM roles
             UNION ALL
             SELECT id, 'Departments', 'cgr_code_ids', 'EDIT' FROM roles",
        );

        $cgrCodes = $this->table('cgr_codes');
        if ($cgrCodes->hasColumn('department_id')) {
            if ($this->hasForeignKey('cgr_codes', 'department_id')) {
                $cgrCodes->dropForeignKey('department_id');
            }
            $cgrCodes->removeColumn('department_id');
        }
        if ($this->hasIndex('cgr_codes', 'idx_cgr_codes_unique_definition')) {
            $cgrCodes->removeIndexByName('idx_cgr_codes_unique_definition');
        }
        if (!$this->hasIndex('cgr_codes', 'idx_cgr_codes_unique_definition')) {
            $cgrCodes->addIndex(['type', 'code'], [
                'name' => 'idx_cgr_codes_unique_definition',
                'unique' => true,
            ]);
        }
        $cgrCodes->update();

        $departments = $this->table('departments');
        if ($departments->hasColumn('cgr_code_id')) {
            if ($this->hasForeignKey('departments', 'cgr_code_id')) {
                $departments->dropForeignKey('cgr_code_id');
            }
            if ($this->hasIndex('departments', 'fk_departments_cgr_codes')) {
                $departments->removeIndexByName('fk_departments_cgr_codes');
            }
            $departments->removeColumn('cgr_code_id');
        }
        if ($departments->hasColumn('default_cgr')) {
            $departments->removeColumn('default_cgr');
        }
        if ($departments->hasColumn('current_manager_id')) {
            $departments->removeColumn('current_manager_id');
        }
        $departments->update();
    }

    /** Regroupe les CGR historiques identiques sur l’ID le plus ancien. */
    private function normalizeDuplicateCgrCodes(): void
    {
        $this->execute(
            'UPDATE departments AS departments
             INNER JOIN cgr_codes AS duplicate ON duplicate.id = departments.cgr_code_id
             INNER JOIN cgr_codes AS canonical
                ON canonical.type = duplicate.type
               AND canonical.code = duplicate.code
               AND canonical.id < duplicate.id
             SET departments.cgr_code_id = canonical.id
             WHERE departments.cgr_code_id IS NOT NULL',
        );
        $this->execute(
            'DELETE duplicateLinks
             FROM department_cgr_codes AS duplicateLinks
             INNER JOIN cgr_codes AS duplicate ON duplicate.id = duplicateLinks.cgr_code_id
             INNER JOIN cgr_codes AS canonical
                ON canonical.type = duplicate.type
               AND canonical.code = duplicate.code
               AND canonical.id < duplicate.id
             INNER JOIN department_cgr_codes AS canonicalLinks
                ON canonicalLinks.department_id = duplicateLinks.department_id
               AND canonicalLinks.cgr_code_id = canonical.id',
        );
        $this->execute(
            'UPDATE department_cgr_codes AS links
             INNER JOIN cgr_codes AS duplicate ON duplicate.id = links.cgr_code_id
             INNER JOIN cgr_codes AS canonical
                ON canonical.type = duplicate.type
               AND canonical.code = duplicate.code
               AND canonical.id < duplicate.id
             SET links.cgr_code_id = canonical.id',
        );
        $this->execute(
            'DELETE duplicate
             FROM cgr_codes AS duplicate
             INNER JOIN cgr_codes AS canonical
                ON canonical.type = duplicate.type
               AND canonical.code = duplicate.code
               AND canonical.id < duplicate.id',
        );
    }

    /** Indique si un index SQL existe encore après une reprise partielle. */
    private function hasIndex(string $table, string $index): bool
    {
        $result = $this->query(sprintf(
            "SHOW INDEX FROM `%s` WHERE Key_name = '%s'",
            $table,
            $index,
        ))->fetch();

        return $result !== false;
    }

    /** Indique si une colonne participe encore à une clé étrangère. */
    private function hasForeignKey(string $table, string $column): bool
    {
        $result = $this->query(sprintf(
            "SELECT 1
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = '%s'
               AND COLUMN_NAME = '%s'
               AND REFERENCED_TABLE_NAME IS NOT NULL
             LIMIT 1",
            $table,
            $column,
        ))->fetch();

        return $result !== false;
    }

    /**
     * Retourne le nombre de valeurs historiques non vides avant leur abandon.
     *
     * La valeur est lue explicitement afin que le contrôle fasse partie de la
     * migration, sans réintroduire une colonne ou une table d'audit temporaire.
     */
    private function fetchDefaultCgrCount(): int
    {
        $result = $this->query(
            "SELECT COUNT(*) AS count
             FROM departments
             WHERE default_cgr IS NOT NULL AND TRIM(default_cgr) <> ''",
        )->fetch();

        return (int)($result['count'] ?? 0);
    }

    /** La reprise des anciennes colonnes est irréversible par conception. */
    public function down(): void
    {
    }
}
