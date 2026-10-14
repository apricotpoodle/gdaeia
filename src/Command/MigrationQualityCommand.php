<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use RuntimeException;

/** Contrôle les exports CSV du premier lot de migration historique. */
final class MigrationQualityCommand extends Command
{
    /** @var list<string> */
    private const REQUIRED_FILES = [
        '01-users.csv',
        '02-roles.csv',
        '03-user-roles.csv',
    ];

    /** @var list<string> */
    private const FORBIDDEN_HEADERS = [
        'USR_PWD',
        'USR_TOKEN_CONF',
        'USR_TOKEN_REG',
        'USR_TOKEN_REM',
    ];

    /**
     * Définit les arguments et options de la commande.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser Analyseur de la ligne de commande.
     * @return \Cake\Console\ConsoleOptionParser Analyseur configuré.
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return parent::buildOptionParser($parser)
            ->setDescription('Contrôle les exports CSV de la migration des utilisateurs et habilitations.')
            ->addArgument('directory', [
                'help' => 'Répertoire contenant les CSV issus de export-habilitations.sql.',
                'required' => true,
            ])
            ->addOption('report-dir', [
                'help' => 'Répertoire de sortie des rapports CSV.',
                'short' => 'r',
                'default' => null,
            ]);
    }

    /**
     * Contrôle les fichiers sans écrire dans le répertoire source.
     *
     * @param \Cake\Console\Arguments $args Arguments de la commande.
     * @param \Cake\Console\ConsoleIo $io Sortie console.
     * @return int Code de sortie.
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $directory = rtrim((string)$args->getArgument('directory'), DIRECTORY_SEPARATOR);
        $reportDirectory = $args->getOption('report-dir');

        try {
            $result = $this->inspect($directory);
            if ($reportDirectory !== null) {
                $this->writeReports((string)$reportDirectory, $result);
            }
        } catch (RuntimeException $exception) {
            $io->error($exception->getMessage());

            return static::CODE_ERROR;
        }

        $io->out(sprintf(
            'Utilisateurs : %d (%d importables, %d sans courriel, %d courriels dupliqués).',
            $result['user_count'],
            $result['importable_user_count'],
            count($result['missing_emails']),
            count($result['duplicate_emails']),
        ));
        $io->out(sprintf(
            'Rôles : %d ; liens utilisateurs-rôles orphelins : %d.',
            $result['role_count'],
            count($result['orphaned_links']),
        ));
        if ($reportDirectory !== null) {
            $io->out('Rapports écrits dans ' . $reportDirectory . '.');
        }

        return static::CODE_SUCCESS;
    }

    /**
     * @return array{user_count: int, importable_user_count: int, role_count: int, missing_emails: list<array<string, string>>, duplicate_emails: list<array<string, string>>, orphaned_links: list<array<string, string>>}
     */
    private function inspect(string $directory): array
    {
        if (!is_dir($directory)) {
            throw new RuntimeException('Le répertoire CSV est introuvable.');
        }

        foreach (self::REQUIRED_FILES as $file) {
            if (!is_file($directory . DIRECTORY_SEPARATOR . $file)) {
                throw new RuntimeException(sprintf('Fichier CSV obligatoire absent : %s.', $file));
            }
        }

        $users = $this->readCsv($directory . DIRECTORY_SEPARATOR . '01-users.csv');
        $roles = $this->readCsv($directory . DIRECTORY_SEPARATOR . '02-roles.csv');
        $userRoles = $this->readCsv($directory . DIRECTORY_SEPARATOR . '03-user-roles.csv');

        $roleIds = array_fill_keys(array_filter(array_column($roles, 'source_role_id')), true);
        $userIds = array_fill_keys(array_filter(array_column($users, 'source_user_id')), true);
        $emailCounts = [];
        $missingEmails = [];
        foreach ($users as $user) {
            $email = strtolower(trim($user['email'] ?? ''));
            if ($email === '') {
                $missingEmails[] = [
                    'source_user_id' => $user['source_user_id'] ?? '',
                    'source_username' => $user['source_username'] ?? '',
                ];
                continue;
            }
            $emailCounts[$email] = ($emailCounts[$email] ?? 0) + 1;
        }

        $duplicateEmails = [];
        foreach ($emailCounts as $email => $count) {
            if ($count > 1) {
                $duplicateEmails[] = ['email' => $email, 'source_user_count' => (string)$count];
            }
        }

        $orphanedLinks = [];
        foreach ($userRoles as $link) {
            if (!isset($userIds[$link['source_user_id'] ?? '']) || !isset($roleIds[$link['source_role_id'] ?? ''])) {
                $orphanedLinks[] = [
                    'source_user_id' => $link['source_user_id'] ?? '',
                    'source_role_id' => $link['source_role_id'] ?? '',
                    'source_company_id' => $link['source_company_id'] ?? '',
                ];
            }
        }

        return [
            'user_count' => count($users),
            'importable_user_count' => count($users) - count($missingEmails) - $this->countDuplicateRows($users),
            'role_count' => count($roles),
            'missing_emails' => $missingEmails,
            'duplicate_emails' => $duplicateEmails,
            'orphaned_links' => $orphanedLinks,
        ];
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
        $headers = array_map(static fn(?string $header): string => trim((string)$header), $headers);
        if (array_intersect(self::FORBIDDEN_HEADERS, $headers) !== []) {
            fclose($handle);
            throw new RuntimeException(sprintf('Donnée sensible détectée dans %s.', basename($path)));
        }

        $rows = [];
        while (($values = fgetcsv($handle)) !== false) {
            $hasValues = count(array_filter(
                $values,
                static fn(?string $value): bool => trim((string)$value) !== '',
            )) > 0;
            if ($values === [null] || !$hasValues) {
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

    /** @param list<array<string, string>> $users */
    private function countDuplicateRows(array $users): int
    {
        $counts = [];
        foreach ($users as $user) {
            $email = strtolower(trim($user['email'] ?? ''));
            if ($email !== '') {
                $counts[$email] = ($counts[$email] ?? 0) + 1;
            }
        }

        return array_sum(array_map(static fn(int $count): int => max(0, $count - 1), $counts));
    }

    /** @param array<string, mixed> $result */
    private function writeReports(string $directory, array $result): void
    {
        if (!is_dir($directory) && !mkdir($directory, 0770, true) && !is_dir($directory)) {
            throw new RuntimeException(
                'Impossible de créer le répertoire des rapports.',
            );
        }
        $this->writeCsv($directory . DIRECTORY_SEPARATOR . 'quality-missing-emails.csv', $result['missing_emails']);
        $this->writeCsv($directory . DIRECTORY_SEPARATOR . 'quality-duplicate-emails.csv', $result['duplicate_emails']);
        $this->writeCsv(
            $directory . DIRECTORY_SEPARATOR . 'quality-orphaned-role-links.csv',
            $result['orphaned_links'],
        );
    }

    /** @param list<array<string, string>> $rows */
    private function writeCsv(string $path, array $rows): void
    {
        $handle = fopen($path, 'wb');
        if ($handle === false) {
            throw new RuntimeException(sprintf('Impossible d’écrire le rapport %s.', basename($path)));
        }
        $headers = $rows === [] ? ['result'] : array_keys($rows[0]);
        fputcsv($handle, $headers);
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
    }
}
