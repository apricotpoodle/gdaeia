<?php
declare(strict_types=1);

namespace App\Service\Migration;

use RuntimeException;

/**
 * Prépare le plan d'import des sociétés, services et codes CGR historiques.
 *
 * Cette classe ne modifie aucune table. Elle transforme les exports CSV en un
 * plan déterministe que la commande d'import pourra valider puis appliquer.
 */
final class DepartmentImportService
{
    /** @var list<string> */
    private const REQUIRED_FILES = [
        '05-user-service-visibility.csv',
        '06-user-cgr-service-scope.csv',
        '07-reference-companies.csv',
        '08-reference-services.csv',
    ];

    /**
     * Construit le plan d'import depuis un répertoire d'exports.
     *
     * @param string $directory Répertoire contenant les CSV.
     * @return array{
     *     companies: list<array{source_id: string, code: string, name: string, order: string}>,
     *     services: list<array{
     *         source_company_id: string,
     *         company_code: string,
     *         source_service_id: string,
     *         source_service_code: string,
     *         name: string,
     *         target_code: string,
     *         cgrs: list<array{source_id: string, code: string, label: string, type: string}>
     *     }>,
     *     cgr_count: int,
     *     cgr_assignment_count: int,
     *     unmapped_service_count: int,
     *     unmapped_services: list<array{source_id: string, code: string, name: string}>
     * }
     */
    public function prepare(string $directory): array
    {
        if (!is_dir($directory)) {
            throw new RuntimeException('Le répertoire CSV est introuvable.');
        }

        foreach (self::REQUIRED_FILES as $file) {
            if (!is_file($directory . DIRECTORY_SEPARATOR . $file)) {
                throw new RuntimeException(sprintf('Fichier CSV obligatoire absent : %s.', $file));
            }
        }

        $companies = $this->readCsv($directory . DIRECTORY_SEPARATOR . '07-reference-companies.csv');
        $serviceReferences = $this->readCsv($directory . DIRECTORY_SEPARATOR . '08-reference-services.csv');
        $visibility = $this->readCsv($directory . DIRECTORY_SEPARATOR . '05-user-service-visibility.csv');
        $scope = $this->readCsv($directory . DIRECTORY_SEPARATOR . '06-user-cgr-service-scope.csv');

        $companyById = [];
        foreach ($companies as $company) {
            $companyById[$company['source_company_id']] = $company;
        }

        $serviceById = [];
        foreach ($serviceReferences as $service) {
            $serviceById[$service['source_service_id']] = $service;
        }

        $pairs = [];
        foreach ($visibility as $row) {
            $this->addServicePair(
                $pairs,
                $row['source_company_id'] ?? '',
                $row['source_company_service_id'] ?? '',
                $companyById,
                $serviceById,
            );
        }
        foreach ($scope as $row) {
            $this->addServicePair(
                $pairs,
                $row['source_company_id'] ?? '',
                $row['source_service_id'] ?? '',
                $companyById,
                $serviceById,
                $row,
            );
        }

        $cgrsByPair = [];
        $cgrIds = [];
        $cgrAssignmentIds = [];
        foreach ($scope as $row) {
            $pairKey = $this->pairKey($row['source_company_id'] ?? '', $row['source_service_id'] ?? '');
            $sourceId = trim($row['source_analytic_code_id'] ?? '');
            $code = trim($row['source_analytic_code'] ?? '');
            if ($pairKey === '|' || $sourceId === '' || $code === '') {
                continue;
            }
            $cgrKey = $pairKey . '|' . $sourceId;
            if (isset($cgrAssignmentIds[$cgrKey])) {
                continue;
            }
            $cgrAssignmentIds[$cgrKey] = true;
            $cgrIds[$sourceId] = true;
            $cgrsByPair[$pairKey][] = [
                'source_id' => $sourceId,
                'code' => $code,
                'label' => trim($row['source_analytic_name'] ?? $code),
                'type' => 'SERVICE',
            ];
        }

        uasort($pairs, static function (array $left, array $right): int {
            return [$left['company_code'], (int)$left['source_service_id']]
                <=> [$right['company_code'], (int)$right['source_service_id']];
        });

        $plannedServices = [];
        foreach ($pairs as $pair) {
            $pairKey = $this->pairKey($pair['source_company_id'], $pair['source_service_id']);
            $plannedServices[] = [
                'source_company_id' => $pair['source_company_id'],
                'company_code' => $pair['company_code'],
                'source_service_id' => $pair['source_service_id'],
                'source_service_code' => $pair['source_service_code'],
                'name' => $pair['name'],
                'target_code' => sprintf(
                    '%s-SRV-%s',
                    $pair['company_code'],
                    $pair['source_service_id'],
                ),
                'cgrs' => $cgrsByPair[$pairKey] ?? [],
            ];
        }

        $mappedServiceIds = [];
        foreach ($pairs as $pair) {
            $mappedServiceIds[$pair['source_service_id']] = true;
        }

        usort($companies, static function (array $left, array $right): int {
            return [(int)$left['source_company_order'], (int)$left['source_company_id']]
                <=> [(int)$right['source_company_order'], (int)$right['source_company_id']];
        });

        $unmappedServices = [];
        foreach ($serviceById as $serviceId => $service) {
            if (!isset($mappedServiceIds[$serviceId])) {
                $unmappedServices[] = [
                    'source_id' => $serviceId,
                    'code' => trim($service['source_service_code']),
                    'name' => trim($service['source_service_name']),
                ];
            }
        }

        return [
            'companies' => array_map(
                static fn(array $company): array => [
                    'source_id' => $company['source_company_id'],
                    'code' => trim($company['source_company_code']),
                    'name' => trim($company['source_company_name']),
                    'order' => trim($company['source_company_order']),
                ],
                $companies,
            ),
            'services' => $plannedServices,
            'cgr_count' => count($cgrIds),
            'cgr_assignment_count' => count($cgrAssignmentIds),
            'unmapped_service_count' => count($unmappedServices),
            'unmapped_services' => $unmappedServices,
        ];
    }

    /**
     * @param array<string, array<string, string>> $pairs
     * @param array<string, array<string, string>> $companyById
     * @param array<string, array<string, string>> $serviceById
     * @param array<string, string> $fallback
     */
    private function addServicePair(
        array &$pairs,
        string $companyId,
        string $serviceId,
        array $companyById,
        array $serviceById,
        array $fallback = [],
    ): void {
        $key = $this->pairKey($companyId, $serviceId);
        if ($key === '|' || isset($pairs[$key]) || !isset($companyById[$companyId])) {
            return;
        }

        $service = $serviceById[$serviceId] ?? $fallback;
        if ($service === []) {
            return;
        }
        $pairs[$key] = [
            'source_company_id' => $companyId,
            'company_code' => trim($companyById[$companyId]['source_company_code']),
            'source_service_id' => $serviceId,
            'source_service_code' => trim($service['source_service_code'] ?? ''),
            'name' => trim($service['source_service_name'] ?? ''),
        ];
    }

    /** Construit la clé stable d’un couple société-service. */
    private function pairKey(string $companyId, string $serviceId): string
    {
        return trim($companyId) . '|' . trim($serviceId);
    }

    /** @return list<array<string, string>> */
    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new RuntimeException(sprintf('Impossible de lire le fichier %s.', basename($path)));
        }

        $headers = fgetcsv($handle);
        if ($headers === false || $headers === [null]) {
            fclose($handle);
            throw new RuntimeException(sprintf('Fichier CSV vide : %s.', basename($path)));
        }
        $headers = array_map(
            static fn(?string $header): string => trim((string)$header, " \t\n\r\0\x0B\xEF\xBB\xBF"),
            $headers,
        );

        $rows = [];
        while (($values = fgetcsv($handle)) !== false) {
            $hasValues = count(array_filter(
                $values,
                static fn(?string $value): bool => trim((string)$value) !== '',
            )) > 0;
            if ($values === [null] || !$hasValues) {
                continue;
            }
            $values = array_pad($values, count($headers), '');
            $normalizedValues = array_map(
                static fn(?string $value): string => (string)$value,
                $values,
            );
            $rows[] = array_combine($headers, $normalizedValues) ?: [];
        }
        fclose($handle);

        return $rows;
    }
}
