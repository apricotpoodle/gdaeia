<?php
declare(strict_types=1);

namespace App\Service\Integrity;

use Cake\Database\Connection;
use Cake\Datasource\ConnectionManager;
use RuntimeException;

/** Contrôle les cibles de la relation polymorphe des commentaires. */
final class CommentsIntegrityChecker implements IntegrityCheckInterface
{
    /** @var array<string, string> */
    private const TARGETS = [
        'Applicationforms' => 'applicationforms',
        'Departments' => 'departments',
        'Users' => 'users',
        'ValidationWorkflowRuns' => 'validation_workflow_runs',
    ];

    /** @param \Cake\Database\Connection|null $connection Connexion injectable. */
    public function __construct(private readonly ?Connection $connection = null)
    {
    }

    /** Identifiant stable du contrôle. */
    public function id(): string
    {
        return 'comments';
    }

    /** @return array<string, mixed> */
    public function check(): array
    {
        $connection = $this->connection ?? ConnectionManager::get('default');
        if (!$connection instanceof Connection) {
            throw new RuntimeException(__('La connexion SQL par défaut est indisponible.'));
        }
        $issues = [];
        $rowsScanned = 0;
        foreach (self::TARGETS as $model => $table) {
            $rows = $connection->execute(
                sprintf(
                    'SELECT c.id AS comment_id, c.foreign_key FROM comments c '
                    . 'LEFT JOIN `%s` target ON target.id = c.foreign_key '
                    . 'WHERE c.model = :model AND target.id IS NULL LIMIT 20',
                    $table,
                ),
                ['model' => $model],
            )->fetchAll('assoc');
            $rowsScanned += count($rows);
            foreach ($rows as $row) {
                $issues[] = [
                    'code' => 'CIBLE_COMMENTAIRE_ABSENTE',
                    'message' => __('Un commentaire cible une ressource inexistante.'),
                    'model' => $model,
                    'details' => $row,
                ];
            }
        }
        $knownModels = "'" . implode("','", array_keys(self::TARGETS)) . "'";
        $unknown = $connection->execute(
            sprintf('SELECT id, model, foreign_key FROM comments WHERE model NOT IN (%s) LIMIT 20', $knownModels),
        )->fetchAll('assoc');
        foreach ($unknown as $row) {
            $issues[] = [
                'code' => 'MODELE_COMMENTAIRE_INCONNU',
                'message' => __('Le modèle polymorphe du commentaire n’est pas catalogué.'),
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
}
