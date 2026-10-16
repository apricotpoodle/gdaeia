<?php
declare(strict_types=1);

namespace App\Service\Integrity;

use Cake\Database\Connection;
use Cake\Datasource\ConnectionManager;
use RuntimeException;

/** Contrôle les références relationnelles physiques et logiques déclarées. */
final class ReferenceIntegrityChecker implements IntegrityCheckInterface
{
    /**
     * @var array<string, bool>
     */
    private array $columnCache = [];

    /**
     * @param \Cake\Database\Connection|null $connection Connexion SQL injectable.
     * @param \App\Service\Integrity\ReferenceCatalog|null $catalog Catalogue injectable.
     * @param list<string>|null $referenceIds Références à contrôler, ou toutes.
     */
    public function __construct(
        private readonly ?Connection $connection = null,
        private readonly ?ReferenceCatalog $catalog = null,
        private readonly ?array $referenceIds = null,
    ) {
    }

    /** Identifiant stable du contrôle. */
    public function id(): string
    {
        return 'references';
    }

    /** @return array<string, mixed> */
    public function check(): array
    {
        $connection = $this->connection ?? ConnectionManager::get('default');
        if (!$connection instanceof Connection) {
            throw new RuntimeException(__('La connexion SQL par défaut est indisponible.'));
        }
        $catalog = $this->catalog ?? new ReferenceCatalog();
        $definitions = $catalog->all();
        if ($this->referenceIds !== null) {
            $definitions = array_intersect_key($definitions, array_flip($this->referenceIds));
        }

        $issues = [];
        $rowsScanned = 0;
        foreach ($definitions as $definition) {
            $rowsScanned += $this->countRows($connection, $definition);
            $issues = [...$issues, ...$this->findIssues($connection, $definition)];
        }

        return [
            'id' => $this->id(),
            'status' => $issues === [] ? 'ok' : 'error',
            'rows_scanned' => $rowsScanned,
            'violations' => count($issues),
            'issues' => $issues,
        ];
    }

    /** Compte les lignes porteuses d’une référence non nulle. */
    private function countRows(Connection $connection, ReferenceDefinition $definition): int
    {
        $source = $this->identifier($definition->sourceTable);
        $column = $this->identifier($definition->sourceColumn);
        $result = $connection->execute(
            sprintf('SELECT COUNT(*) FROM `%s` WHERE `%s` IS NOT NULL', $source, $column),
        );

        return (int)$result->fetchColumn(0);
    }

    /**
     * @return list<array{code: string, message: string, reference: string, source_id: int, target_id: int}>
     */
    private function findIssues(Connection $connection, ReferenceDefinition $definition): array
    {
        $source = $this->identifier($definition->sourceTable);
        $sourceColumn = $this->identifier($definition->sourceColumn);
        $sourceKey = $this->identifier($definition->sourceKey);
        $target = $this->identifier($definition->targetTable);
        $targetColumn = $this->identifier($definition->targetColumn);
        $targetDeleted = $this->hasColumn($connection, $definition->targetTable, 'deleted');
        $sourceDeleted = $definition->sourceDeletedColumn !== null
            && $this->hasColumn($connection, $definition->sourceTable, $definition->sourceDeletedColumn);

        $conditions = [
            't.`' . $targetColumn . '` IS NULL',
        ];
        if ($definition->targetPolicy === 'active' && $targetDeleted) {
            $disabledTarget = 't.`deleted` IS NOT NULL';
            if ($sourceDeleted) {
                $sourceDeletedColumn = $this->identifier((string)$definition->sourceDeletedColumn);
                $disabledTarget = sprintf('(s.`%s` IS NULL AND %s)', $sourceDeletedColumn, $disabledTarget);
            }
            $conditions[] = $disabledTarget;
        }

        $where = implode(' OR ', $conditions);
        $query = sprintf(
            'SELECT s.`%s` AS source_id, s.`%s` AS target_id,
                CASE
                    WHEN t.`%s` IS NULL THEN \'REFERENCE_ABSENTE\'
                    ELSE \'CIBLE_DESACTIVEE\'
                END AS issue_code
             FROM `%s` s
             LEFT JOIN `%s` t ON t.`%s` = s.`%s`
             WHERE s.`%s` IS NOT NULL AND (%s)
             ORDER BY s.`%s`
             LIMIT 20',
            $sourceKey,
            $sourceColumn,
            $targetColumn,
            $source,
            $target,
            $targetColumn,
            $sourceColumn,
            $sourceColumn,
            $where,
            $sourceKey,
        );

        /** @var list<array{source_id: int|string, target_id: int|string, issue_code: string}> $rows */
        $rows = $connection->execute($query)->fetchAll('assoc');

        return array_map(
            static fn(array $row): array => [
                'code' => $row['issue_code'],
                'message' => $row['issue_code'] === 'REFERENCE_ABSENTE'
                    ? __('La référence cible n’existe pas.')
                    : __('La cible active est désactivée.'),
                'reference' => $definition->id,
                'source_id' => (int)$row['source_id'],
                'target_id' => (int)$row['target_id'],
            ],
            $rows,
        );
    }

    /** Indique si une colonne existe dans la base courante. */
    private function hasColumn(Connection $connection, string $table, string $column): bool
    {
        $key = $table . '.' . $column;
        if (array_key_exists($key, $this->columnCache)) {
            return $this->columnCache[$key];
        }
        $result = $connection->execute(
            'SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND COLUMN_NAME = :column_name',
            ['table_name' => $table, 'column_name' => $column],
        );
        $this->columnCache[$key] = (int)$result->fetchColumn(0) > 0;

        return $this->columnCache[$key];
    }

    /** Valide un identifiant SQL issu du catalogue statique. */
    private function identifier(string $identifier): string
    {
        if (preg_match('/^[a-zA-Z0-9_]+$/', $identifier) !== 1) {
            throw new RuntimeException(__('Identifiant SQL non autorisé dans le catalogue.'));
        }

        return $identifier;
    }
}
