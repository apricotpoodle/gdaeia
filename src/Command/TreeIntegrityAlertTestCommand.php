<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\TreeIntegrityAlertService;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;

/** Envoie une alerte TreeBehavior de test sans contrôler ni modifier les données. */
final class TreeIntegrityAlertTestCommand extends Command
{
    /**
     * Vérifie la configuration puis envoie un courriel explicitement marqué comme test.
     *
     * @param \Cake\Console\Arguments $args Arguments de la commande.
     * @param \Cake\Console\ConsoleIo $io La sortie console.
     * @return int Code de succès ou d’erreur.
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $alertService = new TreeIntegrityAlertService();
        $errors = $alertService->configurationErrors();
        if ($errors !== []) {
            foreach ($errors as $error) {
                $io->error($error);
            }

            return static::CODE_ERROR;
        }
        if (!$alertService->sendTest()) {
            $io->error(__('Alerte de test non remise. Consultez logs/email.log.'));

            return static::CODE_ERROR;
        }

        $io->success(__('Alerte de test envoyée à l’opérateur configuré.'));

        return static::CODE_SUCCESS;
    }
}
