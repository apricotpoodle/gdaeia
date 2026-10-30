<?php
declare(strict_types=1);

namespace App\Test\TestCase\Integration;

use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;

/** Vérifie le schéma final produit par la migration du ticket 0024. */
final class NormalizeDepartmentAssociationsTest extends TestCase
{
    public function testLeSchemaFinalExposeLesLiaisonsEtSupprimeLesColonnesHistoriques(): void
    {
        $connection = ConnectionManager::get('test');

        foreach (['department_managers', 'department_cgr_codes'] as $table) {
            $columns = $connection->execute("SHOW COLUMNS FROM `{$table}`")->fetchAll('assoc');
            $columnNames = array_column($columns, 'Field');

            $this->assertContains('department_id', $columnNames);
            $this->assertContains($table === 'department_managers' ? 'user_id' : 'cgr_code_id', $columnNames);
            $this->assertSame(2, count($columnNames));
        }

        $departmentsColumns = array_column(
            $connection->execute('SHOW COLUMNS FROM departments')->fetchAll('assoc'),
            'Field',
        );
        $cgrColumns = array_column(
            $connection->execute('SHOW COLUMNS FROM cgr_codes')->fetchAll('assoc'),
            'Field',
        );

        $this->assertNotContains('current_manager_id', $departmentsColumns);
        $this->assertNotContains('cgr_code_id', $departmentsColumns);
        $this->assertNotContains('default_cgr', $departmentsColumns);
        $this->assertNotContains('department_id', $cgrColumns);
    }

    public function testLesIndexGarantissentLesDoublonsAuNiveauBase(): void
    {
        $connection = ConnectionManager::get('test');

        $managerIndexes = $connection->execute('SHOW INDEX FROM department_managers')->fetchAll('assoc');
        $cgrIndexes = $connection->execute('SHOW INDEX FROM department_cgr_codes')->fetchAll('assoc');
        $catalogIndexes = $connection->execute('SHOW INDEX FROM cgr_codes')->fetchAll('assoc');

        $managerPrimary = array_values(array_filter(
            $managerIndexes,
            static fn(array $index): bool => $index['Key_name'] === 'PRIMARY',
        ));
        $cgrPrimary = array_values(array_filter(
            $cgrIndexes,
            static fn(array $index): bool => $index['Key_name'] === 'PRIMARY',
        ));

        $this->assertSame(
            ['department_id', 'user_id'],
            array_column($managerPrimary, 'Column_name'),
        );
        $this->assertSame(
            ['department_id', 'cgr_code_id'],
            array_column($cgrPrimary, 'Column_name'),
        );

        $globalIndex = array_values(array_filter(
            $catalogIndexes,
            static fn(array $index): bool => $index['Key_name'] === 'idx_cgr_codes_unique_definition',
        ));
        $this->assertSame(['type', 'code'], array_column($globalIndex, 'Column_name'));
        $this->assertSame([0, 0], array_column($globalIndex, 'Non_unique'));

        $duplicates = $connection->execute(
            'SELECT COUNT(*) FROM (
                SELECT type, code FROM cgr_codes GROUP BY type, code HAVING COUNT(*) > 1
            ) AS duplicate_definitions',
        )->fetchColumn(0);
        $this->assertSame(0, (int)$duplicates);

        foreach (
            [
                ['department_managers', 'department_id', 'departments'],
                ['department_managers', 'user_id', 'users'],
                ['department_cgr_codes', 'department_id', 'departments'],
                ['department_cgr_codes', 'cgr_code_id', 'cgr_codes'],
            ] as [$table, $column, $referencedTable]
        ) {
            $foreignKey = $connection->execute(
                'SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = :table_name
                   AND COLUMN_NAME = :column_name
                   AND REFERENCED_TABLE_NAME = :referenced_table',
                [
                    'table_name' => $table,
                    'column_name' => $column,
                    'referenced_table' => $referencedTable,
                ],
            )->fetchColumn(0);
            $this->assertSame(1, (int)$foreignKey);
        }
    }
}
