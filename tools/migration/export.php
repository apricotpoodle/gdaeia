<?php
declare(strict_types=1);

const FORBIDDEN_COLUMNS = [
    'USR_PWD',
    'USR_TOKEN_CONF',
    'USR_TOKEN_REG',
    'USR_TOKEN_REM',
];

/**
 * Exécute chaque requête d'export historique et produit un CSV UTF-8.
 *
 * @return int Code de sortie du script.
 */
function main(): int
{
    $sqlPath = dirname(__DIR__, 2) . '/docs/migration/export-habilitations.sql';
    $outputDirectory = getenv('MIGRATION_EXPORT_DIR') ?: dirname(__DIR__, 2) . '/tmp/migration/exports';
    if (!is_file($sqlPath)) {
        fwrite(STDERR, "Fichier SQL d'export introuvable.\n");

        return 1;
    }

    try {
        $queries = parseExportQueries((string)file_get_contents($sqlPath));
        $pdo = createLegacyConnection();
        ensureDirectory($outputDirectory);
        foreach ($queries as $filename => $query) {
            exportQuery($pdo, $filename, $query, $outputDirectory);
        }
    } catch (Throwable $exception) {
        fwrite(STDERR, $exception->getMessage() . "\n");

        return 1;
    }

    fwrite(STDOUT, sprintf("%d fichiers CSV écrits dans %s.\n", count($queries), $outputDirectory));

    return 0;
}

/**
 * Extrait les requêtes SELECT identifiées par les marqueurs de sortie.
 *
 * @param string $sql Contenu du fichier SQL d'export.
 * @return array<string, string> Nom de fichier et requête associée.
 */
function parseExportQueries(string $sql): array
{
    preg_match_all(
        '/^-- (\d{2}-[a-z0-9-]+\.csv).*?\R(SELECT\b.*?;)/ims',
        $sql,
        $matches,
        PREG_SET_ORDER,
    );
    if ($matches === []) {
        throw new RuntimeException('Aucune requête SELECT exportable n’a été trouvée.');
    }

    $queries = [];
    foreach ($matches as $match) {
        $queries[$match[1]] = trim($match[2]);
    }

    return $queries;
}

/**
 * Crée une connexion PDO dédiée à l'ancienne base.
 *
 * @return PDO Connexion historique en lecture.
 */
function createLegacyConnection(): PDO
{
    $host = requiredEnvironment('LEGACY_DB_HOST');
    $port = getenv('LEGACY_DB_PORT') ?: '3306';
    $database = getenv('LEGACY_DB_NAME') ?: 'dae';
    $username = requiredEnvironment('LEGACY_DB_USERNAME');
    $password = requiredEnvironment('LEGACY_DB_PASSWORD');
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8', $host, $port, $database);

    try {
        return new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        ]);
    } catch (PDOException $exception) {
        throw new RuntimeException('Connexion à la base historique impossible : ' . $exception->getMessage(), 0, $exception);
    }
}

/**
 * Exporte une requête en CSV avec ses en-têtes.
 *
 * @param PDO $pdo Connexion historique.
 * @param string $filename Nom du fichier de sortie.
 * @param string $query Requête SELECT.
 * @param string $outputDirectory Répertoire de sortie.
 * @return void
 */
function exportQuery(PDO $pdo, string $filename, string $query, string $outputDirectory): void
{
    $statement = $pdo->query($query);
    if ($statement === false) {
        throw new RuntimeException(sprintf('Échec de la requête %s.', $filename));
    }

    $headers = [];
    for ($index = 0; $index < $statement->columnCount(); $index++) {
        $metadata = $statement->getColumnMeta($index);
        $headers[] = (string)($metadata['name'] ?? 'column_' . $index);
    }
    if (array_intersect($headers, FORBIDDEN_COLUMNS) !== []) {
        throw new RuntimeException(sprintf('Donnée sensible détectée dans %s.', $filename));
    }

    $path = rtrim($outputDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
    $handle = fopen($path, 'wb');
    if ($handle === false) {
        throw new RuntimeException(sprintf('Impossible d’écrire %s.', $path));
    }
    fputcsv($handle, $headers);
    while (($row = $statement->fetch(PDO::FETCH_NUM)) !== false) {
        fputcsv($handle, $row);
    }
    fclose($handle);
    fwrite(STDOUT, sprintf("%s écrit.\n", $filename));
}

/** @return string Valeur obligatoire. */
function requiredEnvironment(string $name): string
{
    $value = getenv($name);
    if ($value === false || trim($value) === '') {
        throw new RuntimeException(sprintf('Variable d’environnement obligatoire absente : %s.', $name));
    }

    return $value;
}

/** @return void */
function ensureDirectory(string $directory): void
{
    if (!is_dir($directory) && !mkdir($directory, 0770, true) && !is_dir($directory)) {
        throw new RuntimeException(sprintf('Impossible de créer le répertoire %s.', $directory));
    }
}

if (PHP_SAPI === 'cli' && basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) {
    exit(main());
}
