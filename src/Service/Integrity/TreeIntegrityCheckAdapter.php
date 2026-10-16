<?php
declare(strict_types=1);

namespace App\Service\Integrity;

use App\Service\TreeIntegrityChecker;
use Cake\ORM\Locator\LocatorInterface;

/** Adapte le diagnostic historique des arbres au contrat d’audit global. */
final class TreeIntegrityCheckAdapter implements IntegrityCheckInterface
{
    /** @param \Cake\ORM\Locator\LocatorInterface $tableLocator Registre ORM. */
    public function __construct(private readonly LocatorInterface $tableLocator)
    {
    }

    /** Identifiant stable du contrôle. */
    public function id(): string
    {
        return 'trees';
    }

    /** @return array<string, mixed> */
    public function check(): array
    {
        $reports = (new TreeIntegrityChecker($this->tableLocator))->check();
        $issues = [];
        $rowsScanned = 0;
        foreach ($reports as $report) {
            $rowsScanned += (int)$report['node_count'];
            foreach ($report['issues'] as $issue) {
                $issues[] = ['table' => $report['table'], ...$issue];
            }
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
