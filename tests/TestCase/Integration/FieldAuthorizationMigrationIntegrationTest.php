<?php
declare(strict_types=1);

namespace App\Test\TestCase\Integration;

use Cake\Core\Configure;
use Cake\Database\Connection;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;
use Migrations\Migrations;

/** Vérifie la migration historique des cibles d'autorisation. */
final class FieldAuthorizationMigrationIntegrationTest extends TestCase
{
    private const MIGRATION_BEFORE_TARGETS = 20261106100000;

    private const TARGET_MIGRATION = 20261106110000;

    /** Vérifie la conversion des zones et la conservation des droits existants. */
    public function testLesAutorisationsHistoriquesSontConvertiesSansPerteDeDonnees(): void
    {
        $connection = $this->assertUsesDedicatedTestDatabase();
        $migrations = new Migrations();
        $migrations->migrate(['connection' => 'test']);

        $originalFieldAuthorizations = $this->snapshotTable($connection, 'field_authorizations');
        $originalTargets = $this->snapshotTable($connection, 'authorization_targets');
        $originalRoleAutoIncrement = $this->getAutoIncrement($connection, 'roles');
        $migrationTables = $connection->getSchemaCollection()->listTables();
        $legacyMigrationTables = Configure::read('Migrations.legacyTables');
        $migrationTable = $legacyMigrationTables === true
            || ($legacyMigrationTables !== false && in_array('phinxlog', $migrationTables, true))
            ? 'phinxlog'
            : 'cake_migrations';
        $this->assertContains($migrationTable, $migrationTables);
        $originalMigrationHistory = $connection->execute(
            sprintf('SELECT * FROM `%s`', $migrationTable),
        )->fetchAll('assoc');
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
            try {
                $this->restoreTable($connection, $originalFieldAuthorizations);
            } finally {
                try {
                    $this->restoreTable($connection, $originalTargets);
                } finally {
                    try {
                        $this->restoreMigrationHistory($connection, $migrationTable, $originalMigrationHistory);
                        $pending = array_filter(
                            $migrations->status(['connection' => 'test']),
                            static fn(array $migration): bool => $migration['status'] === 'down',
                        );
                        self::assertSame([], $pending, 'Le schéma doit revenir au niveau courant des migrations.');
                    } finally {
                        try {
                            if ($roleId !== null) {
                                $connection->delete('roles', ['id' => $roleId]);
                            }
                        } finally {
                            $this->restoreAutoIncrement($connection, 'roles', $originalRoleAutoIncrement);
                            self::assertSame(
                                $originalRoleAutoIncrement,
                                $this->getAutoIncrement($connection, 'roles'),
                                'La suppression du rôle temporaire doit restaurer AUTO_INCREMENT.',
                            );
                        }
                    }
                }
            }
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

    /**
     * Capture le DDL, les données et le prochain identifiant d'une table.
     *
     * @return array{table: string, create: string, rows: array, autoIncrement: int|null}
     */
    private function snapshotTable(Connection $connection, string $table): array
    {
        $definition = $connection->execute(sprintf('SHOW CREATE TABLE `%s`', $table))->fetch('assoc');
        $status = $connection->execute(
            'SELECT AUTO_INCREMENT FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name',
            ['table_name' => $table],
        )->fetch('assoc');

        return [
            'table' => $table,
            'create' => (string)$definition['Create Table'],
            'rows' => $connection->execute(sprintf('SELECT * FROM `%s`', $table))->fetchAll('assoc'),
            'autoIncrement' => isset($status['AUTO_INCREMENT']) ? (int)$status['AUTO_INCREMENT'] : null,
        ];
    }

    /** Restaure le DDL et les données capturés avant le rollback. */
    private function restoreTable(Connection $connection, array $snapshot): void
    {
        $table = $snapshot['table'];
        $connection->execute(sprintf('DROP TABLE IF EXISTS `%s`', $table));
        $connection->execute($snapshot['create']);
        foreach ($snapshot['rows'] as $row) {
            $connection->insert($table, $row);
        }

        if ($snapshot['autoIncrement'] !== null) {
            $connection->execute(sprintf(
                'ALTER TABLE `%s` AUTO_INCREMENT = %d',
                $table,
                $snapshot['autoIncrement'],
            ));
        }
    }

    /** Restaure les migrations enregistrées avant le test. */
    private function restoreMigrationHistory(Connection $connection, string $table, array $rows): void
    {
        $connection->execute(sprintf('DELETE FROM `%s`', $table));
        foreach ($rows as $row) {
            $connection->insert($table, $row);
        }
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
