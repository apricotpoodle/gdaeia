<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Database\Connection;

/** Partage la détection des étapes dont la séquence de configuration est absente. */
final class ValidationWorkflowOrphanScanner
{
    /** @param \Cake\Database\Connection $connection Connexion SQL injectable. */
    public function __construct(private readonly Connection $connection)
    {
    }

    /** @return list<array<string, mixed>> */
    public function scan(): array
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
JOIN applicationforms af ON af.id = s.applicationform_id
LEFT JOIN validationsequences current_sequence ON current_sequence.id = s.validationsequence_id
LEFT JOIN validationsequences candidate
    ON candidate.department_id = af.department_id
   AND candidate.role_id = s.role_id
   AND candidate.sequence = s.sequence_number
   AND candidate.deleted IS NULL
WHERE s.deleted IS NULL
  AND current_sequence.id IS NULL
GROUP BY s.id, s.applicationform_id, s.validationsequence_id, s.role_id, s.sequence_number, af.department_id
ORDER BY s.applicationform_id, s.id
SQL;

        /** @var list<array<string, mixed>> $rows */
        $rows = $this->connection->execute($query)->fetchAll('assoc');

        return $rows;
    }
}
