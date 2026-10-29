<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Database\Connection;
use Cake\Datasource\ConnectionInterface;
use Cake\Datasource\ConnectionManager;
use InvalidArgumentException;
use RuntimeException;

/**
 * Détecte et réaligne les étapes de workflow dont la séquence de configuration
 * a été supprimée.
 */
final class ValidationWorkflowSequenceRepairService
{
    /**
     * @param \Cake\Database\Connection|null $connection Connexion injectable pour les tests.
     */
    public function __construct(?ConnectionInterface $connection = null)
    {
        if ($connection !== null && !$connection instanceof Connection) {
            throw new InvalidArgumentException('La connexion doit être une connexion SQL CakePHP.');
        }
        $this->connection = $connection;
    }

    private readonly ?Connection $connection;

    /**
     * Analyse les étapes orphelines et, si demandé, applique les correspondances uniques.
     *
     * @return array{
     *     scanned: int,
     *     repairable: int,
     *     repaired: int,
     *     skipped: int,
     *     items: list<array<string, mixed>>
     * }
     */
    public function repair(bool $apply = false): array
    {
        $connection = $this->getConnection();
        $items = $this->findOrphans($connection);
        $repairable = count(array_filter(
            $items,
            static fn(array $item): bool => (int)$item['matching_sequences'] === 1,
        ));
        $repaired = 0;

        if ($apply) {
            $connection->transactional(function () use ($connection, $items, &$repaired): void {
                foreach ($items as $item) {
                    if ((int)$item['matching_sequences'] !== 1) {
                        continue;
                    }
                    $updated = $connection->update(
                        'applicationvalidationsteps',
                        ['validationsequence_id' => (int)$item['matching_sequence_id']],
                        [
                            'id' => (int)$item['step_id'],
                            'validationsequence_id' => (int)$item['missing_sequence_id'],
                        ],
                    );
                    $repaired += $updated->rowCount();
                }
            });
        }

        return [
            'scanned' => count($items),
            'repairable' => $repairable,
            'repaired' => $repaired,
            'skipped' => count($items) - $repairable,
            'items' => $items,
        ];
    }

    /** Retourne la connexion SQL injectée ou la connexion par défaut. */
    private function getConnection(): Connection
    {
        $connection = $this->connection ?? ConnectionManager::get('default');
        if (!$connection instanceof Connection) {
            throw new RuntimeException('La connexion SQL par défaut est indisponible.');
        }

        return $connection;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function findOrphans(Connection $connection): array
    {
        $query = <<<'SQL'
SELECT
    s.id AS step_id,
    s.applicationform_id,
    s.validationsequence_id AS missing_sequence_id,
    s.role_id,
    s.sequence_number,
    af.department_id,
    COUNT(candidate.id) AS matching_sequences,
    MIN(candidate.id) AS matching_sequence_id
FROM applicationvalidationsteps s
JOIN applicationforms af
    ON af.id = s.applicationform_id
LEFT JOIN validationsequences current_sequence
    ON current_sequence.id = s.validationsequence_id
LEFT JOIN validationsequences candidate
    ON candidate.department_id = af.department_id
   AND candidate.role_id = s.role_id
   AND candidate.sequence = s.sequence_number
   AND candidate.deleted IS NULL
WHERE s.deleted IS NULL
  AND current_sequence.id IS NULL
GROUP BY
    s.id,
    s.applicationform_id,
    s.validationsequence_id,
    s.role_id,
    s.sequence_number,
    af.department_id
ORDER BY
    s.applicationform_id,
    s.id
SQL;

        /** @var list<array<string, mixed>> $rows */
        $rows = $connection->execute($query)->fetchAll('assoc');

        return $rows;
    }
}
