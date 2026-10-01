<?php
declare(strict_types=1);

/** Vérifie que chaque fichier PHP de src/Policy atteint 100 % de couverture de lignes. */

if ($argc !== 2) {
    fwrite(STDERR, "Usage : php tools/check-policy-coverage.php <clover.xml>\n");
    exit(2);
}

$reportPath = $argv[1];
if (!is_file($reportPath)) {
    fwrite(STDERR, "Rapport Clover introuvable : {$reportPath}\n");
    exit(2);
}

$report = simplexml_load_file($reportPath);
if ($report === false) {
    fwrite(STDERR, "Rapport Clover invalide : {$reportPath}\n");
    exit(2);
}

$coverage = [];
foreach ($report->xpath('//file') ?: [] as $file) {
    $path = (string)$file['name'];
    if (!str_ends_with($path, '.php') || !str_contains($path, '/Policy/')) {
        continue;
    }

    $statements = 0;
    $covered = 0;
    foreach ($file->line as $line) {
        if ((string)$line['type'] !== 'stmt') {
            continue;
        }

        $statements++;
        if ((int)$line['count'] > 0) {
            $covered++;
        }
    }

    $coverage[$path] = [$covered, $statements];
}

$projectRoot = realpath(__DIR__ . '/..');
$policyRoot = realpath(__DIR__ . '/../src/Policy');
if ($projectRoot === false || $policyRoot === false) {
    fwrite(STDERR, "Répertoire src/Policy introuvable.\n");
    exit(2);
}

$expected = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($policyRoot));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $expected[$file->getRealPath()] = true;
    }
}

$failures = [];
foreach (array_keys($expected) as $path) {
    $metrics = $coverage[$path] ?? null;
    if ($metrics === null) {
        $suffix = str_replace($projectRoot, '', $path);
        foreach ($coverage as $reportedPath => $reportedMetrics) {
            if (str_ends_with($reportedPath, $suffix)) {
                $metrics = $reportedMetrics;
                break;
            }
        }
    }

    if ($metrics === null || $metrics[1] === 0 || $metrics[0] !== $metrics[1]) {
        $failures[] = sprintf(
            '%s (%d/%d lignes)',
            str_replace($projectRoot . '/', '', $path),
            $metrics[0] ?? 0,
            $metrics[1] ?? 0,
        );
    }
}

if ($failures !== []) {
    fwrite(STDERR, "Couverture des policies insuffisante :\n- " . implode("\n- ", $failures) . "\n");
    exit(1);
}

printf("Couverture des policies : 100%% (%d fichiers).\n", count($expected));
