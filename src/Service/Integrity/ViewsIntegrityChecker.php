<?php
declare(strict_types=1);

namespace App\Service\Integrity;

use Cake\Database\Connection;
use Cake\Datasource\ConnectionManager;
use RuntimeException;

/** Vérifie que les vues applicatives attendues sont présentes. */
final class ViewsIntegrityChecker implements IntegrityCheckInterface
{
    /** @var list<string> */
    private const VIEWS = ['urds', 'applicationformstatuses', 'currentvalidationroles', 'validation_visas'];

    /** @param \Cake\Database\Connection|null $connection Connexion injectable. */
    public function __construct(private readonly ?Connection $connection = null)
    {
    }

    /** Identifiant stable du contrôle. */
    public function id(): string
    {
        return 'views';
    }

    /** @return array<string, mixed> */
    public function check(): array
    {
        $connection = $this->connection ?? ConnectionManager::get('default');
        if (!$connection instanceof Connection) {
            throw new RuntimeException(__('La connexion SQL par défaut est indisponible.'));
        }
        $issues = [];
        foreach (self::VIEWS as $view) {
            $exists = (int)$connection->execute(
                'SELECT COUNT(*) FROM information_schema.VIEWS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :view',
                ['view' => $view],
            )->fetchColumn(0) > 0;
            if (!$exists) {
                $issues[] = [
                    'code' => 'VUE_ABSENTE',
                    'message' => __('La vue applicative attendue est absente.'),
                    'view' => $view,
                ];
            }
        }

        return [
            'id' => $this->id(),
            'status' => $issues === [] ? 'ok' : 'error',
            'rows_scanned' => count(self::VIEWS),
            'violations' => count($issues),
            'issues' => $issues,
        ];
    }
}
