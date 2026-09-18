<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/** Affiche le prochain ticket dont toutes les dépendances sont terminées. */
final class BacklogNextCommand extends Command
{
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
        $path = ROOT . DS
            . 'docs' . DS
            . 'backlog' . DS
            . 'validation-applicationform' . DS
            . 'manifest.json';
        $manifest = json_decode((string)file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        if (($manifest['workflow'] ?? null) !== $workflow) {
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
}
