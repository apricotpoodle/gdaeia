<?php
declare(strict_types=1);

const EXPORT_DIRECTORY = '/tmp/migration/exports';
const MAPPING_DIRECTORY = '/tmp/migration/mappings';

/** Construit les référentiels historiques à rapprocher des départements actuels. */
function main(): int
{
    $appRoot = dirname(__DIR__, 2);
    $exportDirectory = $appRoot . EXPORT_DIRECTORY;
    $mappingDirectory = $appRoot . MAPPING_DIRECTORY;

    try {
        $companies = readCsv($exportDirectory . '/07-reference-companies.csv');
        $services = readCsv($exportDirectory . '/08-reference-services.csv');
        $visibility = readCsv($exportDirectory . '/05-user-service-visibility.csv');
        $cgrScopes = readCsv($exportDirectory . '/06-user-cgr-service-scope.csv');
        ensureDirectory($mappingDirectory);

        $departments = buildDepartments($companies, $services, $visibility, $cgrScopes);
        $cgrCorrespondence = buildCgrCorrespondence($cgrScopes, $companies);
        writeCsv($mappingDirectory . '/old-departments.csv', $departments);
        writeCsv($mappingDirectory . '/cgr-department-correspondence.csv', $cgrCorrespondence);
    } catch (Throwable $exception) {
        fwrite(STDERR, $exception->getMessage() . "\n");

        return 1;
    }

    fwrite(STDOUT, sprintf(
        "%d départements historiques et %d correspondances CGR écrits dans %s.\n",
        count($departments),
        count($cgrCorrespondence),
        $mappingDirectory,
    ));

    return 0;
}

/** @return list<array<string, string>> */
function readCsv(string $path): array
{
    $handle = fopen($path, 'rb');
    if ($handle === false) {
        throw new RuntimeException(sprintf('Fichier CSV absent ou illisible : %s.', basename($path)));
    }
    $headers = fgetcsv($handle);
    if ($headers === false) {
        fclose($handle);
        throw new RuntimeException(sprintf('Fichier CSV vide : %s.', basename($path)));
    }
    $headers = array_map(static fn(?string $header): string => trim((string)$header), $headers);
    $rows = [];
    while (($values = fgetcsv($handle)) !== false) {
        if ($values === [null]) {
            continue;
        }
        $normalizedValues = array_map(
            static fn(?string $value): string => (string)$value,
            array_pad($values, count($headers), ''),
        );
        $rows[] = array_combine($headers, $normalizedValues) ?: [];
    }
    fclose($handle);

    return $rows;
}

/** @param list<array<string, string>> $companies @param list<array<string, string>> $services @param list<array<string, string>> $visibility @param list<array<string, string>> $cgrScopes @return list<array<string, string>> */
function buildDepartments(array $companies, array $services, array $visibility, array $cgrScopes): array
{
    $companyVisibility = countBy($visibility, 'source_company_id');
    $companyCgr = countBy($cgrScopes, 'source_company_id');
    $serviceVisibility = countBy($visibility, 'source_company_service_id');
    $serviceCgr = countBy($cgrScopes, 'source_service_id');
    $departments = [];

    foreach ($companies as $company) {
        $id = $company['source_company_id'] ?? '';
        $departments[] = [
            'source_type' => 'company',
            'source_id' => $id,
            'source_code' => $company['source_company_code'] ?? '',
            'source_name' => $company['source_company_name'] ?? '',
            'source_order' => $company['source_company_order'] ?? '',
            'visibility_rows' => (string)($companyVisibility[$id] ?? 0),
            'cgr_scope_rows' => (string)($companyCgr[$id] ?? 0),
            'target_department_id' => '',
            'mapping_status' => 'rapprochement_propose',
            'mapping_comment' => 'Proposition de département racine fondée sur la société historique ; confirmer le département cible.',
        ];
    }
    foreach ($services as $service) {
        $id = $service['source_service_id'] ?? '';
        $departments[] = [
            'source_type' => 'service',
            'source_id' => $id,
            'source_code' => $service['source_service_code'] ?? '',
            'source_name' => $service['source_service_name'] ?? '',
            'source_order' => $service['source_service_order'] ?? '',
            'visibility_rows' => (string)($serviceVisibility[$id] ?? 0),
            'cgr_scope_rows' => (string)($serviceCgr[$id] ?? 0),
            'target_department_id' => '',
            'mapping_status' => 'rapprochement_propose',
            'mapping_comment' => 'Proposition de département fondée sur le code et le libellé historiques ; confirmer le rattachement dans l’arbre cible.',
        ];
    }

    return $departments;
}

/** @param list<array<string, string>> $rows @return array<string, int> */
function countBy(array $rows, string $field): array
{
    $counts = [];
    foreach ($rows as $row) {
        $value = $row[$field] ?? '';
        if ($value !== '') {
            $counts[$value] = ($counts[$value] ?? 0) + 1;
        }
    }

    return $counts;
}

/** @param list<array<string, string>> $rows @param list<array<string, string>> $companies @return list<array<string, string>> */
function buildCgrCorrespondence(array $rows, array $companies): array
{
    $companyNames = [];
    foreach ($companies as $company) {
        $companyNames[$company['source_company_id'] ?? ''] = $company['source_company_name'] ?? '';
    }
    $correspondence = [];
    foreach ($rows as $row) {
        $key = implode('|', [
            $row['source_company_id'] ?? '',
            $row['source_service_id'] ?? '',
            $row['source_analytic_code_id'] ?? '',
        ]);
        $correspondence[$key] = [
            'source_company_id' => $row['source_company_id'] ?? '',
            'source_company_code' => $row['source_company_code'] ?? '',
            'source_company_name' => $companyNames[$row['source_company_id'] ?? ''] ?? '',
            'source_service_id' => $row['source_service_id'] ?? '',
            'source_service_code' => $row['source_service_code'] ?? '',
            'source_service_name' => $row['source_service_name'] ?? '',
            'source_analytic_code_id' => $row['source_analytic_code_id'] ?? '',
            'source_analytic_code' => $row['source_analytic_code'] ?? '',
            'source_analytic_name' => $row['source_analytic_name'] ?? '',
            'target_company_department_id' => '',
            'target_service_department_id' => '',
            'mapping_status' => 'a_rapprocher',
            'mapping_comment' => '',
        ];
    }

    return array_values($correspondence);
}

function ensureDirectory(string $directory): void
{
    if (!is_dir($directory) && !mkdir($directory, 0770, true) && !is_dir($directory)) {
        throw new RuntimeException(sprintf('Impossible de créer %s.', $directory));
    }
}

/** @param list<array<string, string>> $rows */
function writeCsv(string $path, array $rows): void
{
    $handle = fopen($path, 'wb');
    if ($handle === false) {
        throw new RuntimeException(sprintf('Impossible d’écrire %s.', $path));
    }
    if ($rows !== []) {
        fputcsv($handle, array_keys($rows[0]));
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
    }
    fclose($handle);
}

if (PHP_SAPI === 'cli' && basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) {
    exit(main());
}
