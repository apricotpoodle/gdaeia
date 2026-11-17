<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\CommandFactoryInterface;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/** Affiche le prochain ticket dont toutes les dépendances sont terminées. */
final class BacklogNextCommand extends Command
{
    /**
     * @param \Cake\Console\CommandFactoryInterface|null $factory Fabrique CakePHP.
     * @param string|null $manifestPath Chemin du manifeste ou du backlog, surchargeable en test.
     */
    public function __construct(?CommandFactoryInterface $factory = null, private readonly ?string $manifestPath = null)
    {
        parent::__construct($factory);
    }

    /**
     * Définit les arguments de la commande.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser Analyseur d'options.
     * @return \Cake\Console\ConsoleOptionParser Analyseur configuré.
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return parent::buildOptionParser($parser)->addArgument('workflow', ['required' => true]);
    }

    /**
     * Affiche le prochain ticket exécutable du workflow demandé.
     *
     * @param \Cake\Console\Arguments $args Arguments de la commande.
     * @param \Cake\Console\ConsoleIo $io Sortie de la console.
     * @return int Code de sortie de la commande.
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $workflow = (string)$args->getArgument('workflow');
        $backlogPath = $this->manifestPath ?? ROOT . DS
            . 'docs' . DS
            . 'backlog';
        $manifestPaths = is_file($backlogPath)
            ? [$backlogPath]
            : $this->findManifestPaths($backlogPath);
        $manifest = null;
        foreach ($manifestPaths as $manifestPath) {
            $candidate = json_decode((string)file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);
            if (($candidate['workflow'] ?? null) === $workflow) {
                $manifest = $candidate;

                break;
            }
        }
        if ($manifest === null) {
            $io->error(__('Workflow inconnu.'));

            return static::CODE_ERROR;
        }
        $status = array_column($manifest['tickets'], 'status', 'id');
        foreach ($manifest['tickets'] as $ticket) {
            if ($ticket['status'] === 'Terminé') {
                continue;
            }
            if (
                array_filter(
                    $ticket['dependsOn'],
                    static fn(string $id): bool => ($status[$id] ?? null) !== 'Terminé',
                ) === []
            ) {
                $io->out($ticket['id'] . ' — ' . $ticket['path']);

                return static::CODE_SUCCESS;
            }
        }
        $io->out(__('Aucun ticket exécutable.'));

        return static::CODE_SUCCESS;
    }

    /**
     * Trouve les manifestes de workflow présents dans le backlog.
     *
     * @param string $backlogPath Répertoire racine du backlog.
     * @return list<string> Chemins triés des manifestes trouvés.
     */
    private function findManifestPaths(string $backlogPath): array
    {
        if (!is_dir($backlogPath)) {
            return [];
        }

        $manifestPaths = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($backlogPath, FilesystemIterator::SKIP_DOTS),
        );
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getFilename() === 'manifest.json') {
                $manifestPaths[] = $file->getPathname();
            }
        }
        sort($manifestPaths, SORT_STRING);

        return $manifestPaths;
    }
}
