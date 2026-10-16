<?php
declare(strict_types=1);

namespace App\Service\Integrity;

use App\Service\ValidationWorkflowOrphanScanner;
use Cake\Database\Connection;
use Cake\Datasource\ConnectionManager;
use RuntimeException;

/** Vérifie les invariants propres au cycle de validation immuable. */
final class ValidationWorkflowIntegrityChecker implements IntegrityCheckInterface
{
    /** @param \Cake\Database\Connection|null $connection Connexion injectable. */
    public function __construct(private readonly ?Connection $connection = null)
    {
    }

    /** Identifiant stable du contrôle. */
    public function id(): string
    {
        return 'workflow';
    }

    /** @return array<string, mixed> */
    public function check(): array
    {
        $connection = $this->connection ?? ConnectionManager::get('default');
        if (!$connection instanceof Connection) {
            throw new RuntimeException(__('La connexion SQL par défaut est indisponible.'));
        }
        $issues = [];
        $queries = [
            'RUN_SANS_ETAPES' => <<<'SQL'
                SELECT wr.id AS run_id
                FROM validation_workflow_runs wr
                LEFT JOIN applicationvalidationsteps s ON s.validation_workflow_run_id = wr.id AND s.deleted IS NULL
                WHERE s.id IS NULL
                LIMIT 20
                SQL,
            'ETAPE_HORS_DEMANDE' => <<<'SQL'
                SELECT s.id AS step_id
                FROM applicationvalidationsteps s
                JOIN validation_workflow_runs wr ON wr.id = s.validation_workflow_run_id
                WHERE s.deleted IS NULL AND s.applicationform_id <> wr.applicationform_id
                LIMIT 20
                SQL,
            'SEQUENCE_DUPLIQUEE' => <<<'SQL'
                SELECT validation_workflow_run_id AS run_id, sequence_number, COUNT(*) AS occurrences
                FROM applicationvalidationsteps
                WHERE deleted IS NULL AND validation_workflow_run_id IS NOT NULL
                GROUP BY validation_workflow_run_id, sequence_number
                HAVING COUNT(*) > 1
                LIMIT 20
                SQL,
        ];
        $rowsScanned = 0;
        foreach ($queries as $code => $query) {
            $rows = $connection->execute($query)->fetchAll('assoc');
            $rowsScanned += count($rows);
            foreach ($rows as $row) {
                $issues[] = [
                    'code' => $code,
                    'message' => $this->message($code),
                    'details' => $row,
                ];
            }
        }
        foreach ((new ValidationWorkflowOrphanScanner($connection))->scan() as $row) {
            $rowsScanned++;
            $issues[] = [
                'code' => 'SEQUENCE_ORPHELINE',
                'message' => __('Une étape active référence une séquence absente.'),
                'details' => $row,
            ];
        }

        return [
            'id' => $this->id(),
            'status' => $issues === [] ? 'ok' : 'error',
            'rows_scanned' => $rowsScanned,
            'violations' => count($issues),
            'issues' => $issues,
        ];
    }

    /** Retourne le message métier associé à un code d’anomalie. */
    private function message(string $code): string
    {
        return match ($code) {
            'RUN_SANS_ETAPES' => __('Un cycle de validation ne contient aucune étape active.'),
            'ETAPE_HORS_DEMANDE' => __('Une étape appartient à une demande différente de celle du cycle.'),
            default => __('Plusieurs étapes partagent le même numéro dans un cycle.'),
        };
    }
}
