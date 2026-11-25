<?php
declare(strict_types=1);

namespace App\Test\TestCase\Integration;

use Cake\Database\Connection;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;
use Migrations\Migrations;

/** Vérifie la migration historique des cibles d'autorisation. */
final class FieldAuthorizationMigrationIntegrationTest extends TestCase
{
    /** @var list<string> Tables dont les données peuvent changer au rejeu des migrations. */
    private const MIGRATION_DATA_TABLES = [
        'authorization_targets',
        'field_authorizations',
        'field_definitions',
        'menus',
        'permissions',
        'permission_scopes',
        'role_permissions',
    ];

    private const MIGRATION_BEFORE_TARGETS = 20261106100000;

    private const TARGET_MIGRATION = 20261106110000;

    private const PREVIOUS_MIGRATION = 20261118150000;

    private const APPLICANT_NAME_MIGRATION = 20261120100000;

    /** Vérifie le catalogue et l'héritage des droits des noms de candidat. */
    public function testLesChampsNomEtPrenomReprennentLesDroitsDuNomHistorique(): void
    {
        $connection = $this->assertUsesDedicatedTestDatabase();
        $migrations = new Migrations();
        $migrations->migrate(['connection' => 'test']);

        $originalMigrationData = $this->snapshotMigrationData($connection);
        $originalRoleAutoIncrement = $this->getAutoIncrement($connection, 'roles');
        $roleId = null;

        try {
            $migrations->rollback([
                'connection' => 'test',
                'target' => self::PREVIOUS_MIGRATION,
            ]);

            $roleId = $this->createMigrationTestRole($connection);
            $this->insertApplicantNameAuthorizations($connection, $roleId);

            $roleIds = array_column(
                $connection->execute('SELECT id FROM roles')->fetchAll('assoc'),
                'id',
            );
            $sourceAccessLevels = [];
            foreach (
                $connection->execute(
                    "SELECT role_id, access_level FROM field_authorizations
                     WHERE resource = 'Applicationforms'
                       AND target_type = 'field'
                       AND target = 'applicantname'",
                )->fetchAll('assoc') as $authorization
            ) {
                $sourceAccessLevels[$authorization['role_id']] = $authorization['access_level'];
            }
            $managedAccessLevels = [];
            foreach (
                $connection->execute(
                    "SELECT role_id, target, access_level FROM field_authorizations
                     WHERE resource = 'Applicationforms'
                       AND target_type = 'field'
                       AND target IN ('applicant_first_name', 'applicant_last_name')",
                )->fetchAll('assoc') as $authorization
            ) {
                $managedAccessLevels[$authorization['role_id']][$authorization['target']] =
                    $authorization['access_level'];
            }

            $migrations->migrate([
                'connection' => 'test',
                'target' => self::APPLICANT_NAME_MIGRATION,
            ]);

            $definitions = $connection->execute(
                "SELECT field FROM field_definitions
                 WHERE resource = 'Applicationforms'
                   AND field IN ('applicant_first_name', 'applicant_last_name')
                 ORDER BY field",
            )->fetchAll('assoc');
            $this->assertSame(
                ['applicant_first_name', 'applicant_last_name'],
                array_column($definitions, 'field'),
            );

            $authorizations = $connection->execute(
                "SELECT role_id, target, access_level FROM field_authorizations
                 WHERE resource = 'Applicationforms'
                   AND target_type = 'field'
                   AND target IN ('applicant_first_name', 'applicant_last_name')
                 ORDER BY role_id, target",
            )->fetchAll('assoc');
            $this->assertCount(count($roleIds) * 2, $authorizations);
            $actualAccessLevels = [];
            foreach ($authorizations as $authorization) {
                $actualAccessLevels[$authorization['role_id']][$authorization['target']] =
                    $authorization['access_level'];
            }

            foreach ($roleIds as $currentRoleId) {
                $expectedAccessLevel = $sourceAccessLevels[$currentRoleId] ?? 'NONE';
                foreach (['applicant_first_name', 'applicant_last_name'] as $field) {
                    $expected = $managedAccessLevels[$currentRoleId][$field] ?? $expectedAccessLevel;
                    $this->assertSame(
                        $expected,
                        $actualAccessLevels[$currentRoleId][$field] ?? null,
                        sprintf('Le champ %s doit suivre le droit de applicantname pour le rôle %s.', $field, $currentRoleId),
                    );
                }
            }
        } finally {
            $this->restoreCurrentMigrationState(
                $connection,
                $migrations,
                $roleId,
                $originalMigrationData,
                $originalRoleAutoIncrement,
            );
        }
    }

    /** Vérifie la conversion des zones et la conservation des droits existants. */
    public function testLesAutorisationsHistoriquesSontConvertiesSansPerteDeDonnees(): void
    {
        $connection = $this->assertUsesDedicatedTestDatabase();
        $migrations = new Migrations();
        $migrations->migrate(['connection' => 'test']);

        $originalMigrationData = $this->snapshotMigrationData($connection);
        $originalRoleAutoIncrement = $this->getAutoIncrement($connection, 'roles');
        $roleId = null;

        try {
            $migrations->rollback([
                'connection' => 'test',
                'target' => self::MIGRATION_BEFORE_TARGETS,
            ]);

            $roleId = $this->createMigrationTestRole($connection);
            $this->insertLegacyAuthorizations($connection, $roleId);

            $before = $connection->execute(
                'SELECT id, role_id, resource, field, access_level, created, modified
                 FROM field_authorizations
                 WHERE role_id = :role_id
                 ORDER BY field',
                ['role_id' => $roleId],
            )->fetchAll('assoc');

            $this->assertCount(3, $before);
            $beforeByField = [];
            foreach ($before as $row) {
                $beforeByField[$row['field']] = $row;
            }

            $migrations->migrate([
                'connection' => 'test',
                'target' => self::TARGET_MIGRATION,
            ]);

            $after = $connection->execute(
                'SELECT id, role_id, resource, target_type, target, access_level, created, modified
                 FROM field_authorizations
                 WHERE role_id = :role_id
                 ORDER BY target',
                ['role_id' => $roleId],
            )->fetchAll('assoc');

            $this->assertSame(['job_section', 'jobtitle', 'rh_section'], array_column($after, 'target'));
            $afterByTarget = [];
            foreach ($after as $row) {
                $afterByTarget[$row['target']] = $row;
            }

            $expectedTypes = [
                'job_section' => 'section',
                'jobtitle' => 'field',
                'rh_section' => 'section',
            ];
            foreach ($expectedTypes as $target => $targetType) {
                $historical = $beforeByField[$target];
                $this->assertSame(
                    [
                        'id' => $historical['id'],
                        'role_id' => $historical['role_id'],
                        'resource' => $historical['resource'],
                        'target_type' => $targetType,
                        'target' => $target,
                        'access_level' => $historical['access_level'],
                        'created' => $historical['created'],
                        'modified' => $historical['modified'],
                    ],
                    $afterByTarget[$target],
                );
            }
        } finally {
            $this->restoreCurrentMigrationState(
                $connection,
                $migrations,
                $roleId,
                $originalMigrationData,
                $originalRoleAutoIncrement,
            );
        }
    }

    /** Refuse tout endpoint autre que daetf2_test explicitement configuré. */
    private function assertUsesDedicatedTestDatabase(): Connection
    {
        $databaseUrl = getenv('DATABASE_TEST_URL');
        $this->assertNotFalse($databaseUrl, 'DATABASE_TEST_URL doit être défini pour ce test.');
        $this->assertNotSame('', $databaseUrl, 'DATABASE_TEST_URL ne peut pas être vide.');

        $url = parse_url((string)$databaseUrl);
        $this->assertIsArray($url);
        $this->assertContains(strtolower((string)($url['scheme'] ?? '')), ['mysql', 'mariadb']);
        $this->assertSame('daetf2_test', ltrim((string)($url['path'] ?? ''), '/'));

        $connection = ConnectionManager::get('test');
        $selectedDatabase = $connection->execute('SELECT DATABASE()')->fetchColumn(0);
        $this->assertSame('daetf2_test', $selectedDatabase);

        return $connection;
    }

    /** Crée un rôle isolé pour respecter la clé étrangère historique. */
    private function createMigrationTestRole(Connection $connection): int
    {
        $token = bin2hex(random_bytes(6));
        $statement = $connection->execute(
            'INSERT INTO roles (base, code, name, sort)
             VALUES (0, :code, :name, :sort)',
            [
                'code' => 'MIG' . $token,
                'name' => 'Migration test ' . $token,
                'sort' => 'Migration test ' . $token,
            ],
        );

        return (int)$statement->lastInsertId();
    }

    /** Ajoute les deux anciennes zones et un champ ORM sous la forme historique. */
    private function insertLegacyAuthorizations(Connection $connection, int $roleId): void
    {
        foreach (
            [
                ['field' => 'job_section', 'access_level' => 'EDIT'],
                ['field' => 'rh_section', 'access_level' => 'VIEW'],
                ['field' => 'jobtitle', 'access_level' => 'NONE'],
            ] as $authorization
        ) {
            $connection->execute(
                'INSERT INTO field_authorizations (role_id, resource, field, access_level)
                 VALUES (:role_id, :resource, :field, :access_level)',
                [
                    'role_id' => $roleId,
                    'resource' => 'Applicationforms',
                    'field' => $authorization['field'],
                    'access_level' => $authorization['access_level'],
                ],
            );
        }
    }

    /** Prépare un droit de référence et un droit administré à préserver. */
    private function insertApplicantNameAuthorizations(Connection $connection, int $roleId): void
    {
        foreach (
            [
                ['target' => 'applicantname', 'access_level' => 'VIEW'],
                ['target' => 'applicant_first_name', 'access_level' => 'NONE'],
            ] as $authorization
        ) {
            $connection->insert('field_authorizations', [
                'role_id' => $roleId,
                'resource' => 'Applicationforms',
                'target_type' => 'field',
                'target' => $authorization['target'],
                'access_level' => $authorization['access_level'],
                'created' => '2026-11-20 09:00:00',
            ]);
        }
    }

    /** Capture les données touchées quand les migrations sont rejouées. */
    private function snapshotMigrationData(Connection $connection): array
    {
        $snapshots = [];
        foreach (self::MIGRATION_DATA_TABLES as $table) {
            $snapshots[] = $this->snapshotTable($connection, $table);
        }

        return $snapshots;
    }

    /**
     * Capture les données et le prochain identifiant d'une table.
     *
     * @return array{table: string, rows: array, autoIncrement: int|null}
     */
    private function snapshotTable(Connection $connection, string $table): array
    {
        $status = $connection->execute(
            'SELECT AUTO_INCREMENT FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name',
            ['table_name' => $table],
        )->fetch('assoc');

        return [
            'table' => $table,
            'rows' => $connection->execute(sprintf('SELECT * FROM `%s`', $table))->fetchAll('assoc'),
            'autoIncrement' => isset($status['AUTO_INCREMENT']) ? (int)$status['AUTO_INCREMENT'] : null,
        ];
    }

    /**
     * Réapplique les migrations récentes et restaure les données sauvegardées.
     *
     * @param list<array{table: string, rows: array, autoIncrement: int|null}> $snapshots
     */
    private function restoreCurrentMigrationState(
        Connection $connection,
        Migrations $migrations,
        ?int $roleId,
        array $snapshots,
        int $originalRoleAutoIncrement,
    ): void {
        if ($roleId !== null) {
            $connection->delete('field_authorizations', ['role_id' => $roleId]);
            $connection->delete('roles', ['id' => $roleId]);
        }

        // Laisse Phinx faire avancer l'historique avec le schéma.
        $migrations->migrate(['connection' => 'test']);

        $this->restoreTables($connection, $snapshots);
        $this->restoreAutoIncrement($connection, 'roles', $originalRoleAutoIncrement);
        self::assertSame(
            $originalRoleAutoIncrement,
            $this->getAutoIncrement($connection, 'roles'),
            'La suppression du rôle temporaire doit restaurer AUTO_INCREMENT.',
        );

        $pending = array_filter(
            $migrations->status(['connection' => 'test']),
            static fn(array $migration): bool => $migration['status'] === 'down',
        );
        self::assertSame([], $pending, 'Le schéma doit revenir au niveau courant des migrations.');

        $deliveryTableCount = (int)$connection->execute(
            'SELECT COUNT(*) FROM information_schema.tables
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name',
            ['table_name' => 'notification_deliveries'],
        )->fetchColumn(0);
        self::assertSame(
            1,
            $deliveryTableCount,
            'La table notification_deliveries doit exister après le nettoyage des tests de migration.',
        );
    }

    /** Restaure l'état des tables qui ont été modifiées par les migrations. */
    private function restoreTables(Connection $connection, array $snapshots): void
    {
        $connection->execute('SET FOREIGN_KEY_CHECKS = 0');
        try {
            foreach ($snapshots as $snapshot) {
                $connection->execute(sprintf('DELETE FROM `%s`', $snapshot['table']));
            }

            foreach ($snapshots as $snapshot) {
                foreach ($snapshot['rows'] as $row) {
                    $connection->insert($snapshot['table'], $row);
                }
                if ($snapshot['autoIncrement'] !== null) {
                    $this->restoreAutoIncrement(
                        $connection,
                        $snapshot['table'],
                        $snapshot['autoIncrement'],
                    );
                }
            }
        } finally {
            $connection->execute('SET FOREIGN_KEY_CHECKS = 1');
        }

        foreach ($snapshots as $snapshot) {
            $rows = $connection->execute(
                sprintf('SELECT * FROM `%s`', $snapshot['table']),
            )->fetchAll('assoc');
            self::assertSame(
                $this->sortRows($snapshot['rows']),
                $this->sortRows($rows),
                sprintf('Les données de %s doivent être restaurées après le test de migration.', $snapshot['table']),
            );
        }
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    private function sortRows(array $rows): array
    {
        usort($rows, static fn(array $left, array $right): int => strcmp(serialize($left), serialize($right)));

        return $rows;
    }

    /** Retourne le prochain identifiant automatique d'une table MySQL. */
    private function getAutoIncrement(Connection $connection, string $table): int
    {
        $value = $connection->execute(
            'SELECT AUTO_INCREMENT FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name',
            ['table_name' => $table],
        )->fetchColumn(0);

        $this->assertIsNumeric($value, sprintf('AUTO_INCREMENT doit exister pour %s.', $table));

        return (int)$value;
    }

    /** Restaure le prochain identifiant automatique après suppression du rôle. */
    private function restoreAutoIncrement(Connection $connection, string $table, int $value): void
    {
        $connection->execute(sprintf('ALTER TABLE `%s` AUTO_INCREMENT = %d', $table, $value));
    }
}
