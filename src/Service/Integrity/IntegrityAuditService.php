<?php
declare(strict_types=1);

namespace App\Service\Integrity;

use InvalidArgumentException;

/** Orchestre les contrôles d’intégrité sans effectuer de mutation. */
final class IntegrityAuditService
{
    /**
     * @param list<\App\Service\Integrity\IntegrityCheckInterface> $checks Contrôles disponibles.
     */
    public function __construct(private readonly array $checks)
    {
    }

    /**
     * Exécute tous les contrôles ou un domaine ciblé.
     *
     * @return array{
     *     success: bool,
     *     summary: array{checks: int, rows_scanned: int, errors: int, warnings: int},
     *     reports: list<array<string, mixed>>
     * }
     */
    public function audit(?string $scope = null): array
    {
        $available = array_map(
            static fn(IntegrityCheckInterface $check): string => $check->id(),
            $this->checks,
        );
        if ($scope !== null && $scope !== 'all' && !in_array($scope, $available, true)) {
            throw new InvalidArgumentException(__(
                'Domaine d’intégrité inconnu : {0}. Domaines acceptés : {1}.',
                $scope,
                implode(', ', $available),
            ));
        }

        $reports = [];
        foreach ($this->checks as $check) {
            if ($scope !== null && $scope !== 'all' && $check->id() !== $scope) {
                continue;
            }
            $reports[] = $check->check();
        }

        $errors = array_sum(array_map(
            static fn(array $report): int => $report['status'] === 'error'
                ? (int)$report['violations']
                : 0,
            $reports,
        ));
        $warnings = array_sum(array_map(
            static fn(array $report): int => $report['status'] === 'warning'
                ? (int)$report['violations']
                : 0,
            $reports,
        ));

        return [
            'success' => $errors === 0,
            'summary' => [
                'checks' => count($reports),
                'rows_scanned' => array_sum(array_map(
                    static fn(array $report): int => (int)$report['rows_scanned'],
                    $reports,
                )),
                'errors' => $errors,
                'warnings' => $warnings,
            ],
            'reports' => $reports,
        ];
    }
}
