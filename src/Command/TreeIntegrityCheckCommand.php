<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\TreeIntegrityAlertService;
use App\Service\TreeIntegrityChecker;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use InvalidArgumentException;

/** Vérifie, sans modifier les données, les arbres TreeBehavior de l'application. */
final class TreeIntegrityCheckCommand extends Command
{
    /**
     * Configure le filtrage de table et les formats de rapport disponibles.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser Le parseur de la commande.
     * @return \Cake\Console\ConsoleOptionParser Le parseur enrichi.
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return parent::buildOptionParser($parser)
            ->addOption('table', [
                'help' => 'Table à vérifier : departments ou menus. Par défaut, toutes les tables sont contrôlées.',
                'choices' => TreeIntegrityChecker::tableNames(),
            ])
            ->addOption('format', [
                'help' => 'Format de sortie : text ou json.',
                'default' => 'text',
                'choices' => ['text', 'json'],
            ])
            ->addOption('check-config', [
                'help' => 'Vérifie les paramètres nécessaires aux alertes sans interroger les données.',
                'boolean' => true,
            ]);
    }

    /**
     * Exécute le diagnostic et retourne une erreur exploitable par la supervision.
     *
     * @param \Cake\Console\Arguments $args Les options saisies.
     * @param \Cake\Console\ConsoleIo $io La sortie console.
     * @return int Code de succès ou d’erreur.
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        if ($args->getOption('check-config') === true) {
            return $this->checkAlertConfiguration($io);
        }
        $tableName = $args->getOption('table');
        try {
            $checker = new TreeIntegrityChecker(TableRegistry::getTableLocator());
            $reports = $checker->check(is_string($tableName) ? $tableName : null);
        } catch (InvalidArgumentException $exception) {
            $io->error($exception->getMessage());

            return static::CODE_ERROR;
        }

        $success = array_reduce(
            $reports,
            static fn(bool $ok, array $report): bool => $ok && $report['success'] === true,
            true,
        );
        $jsonOutput = $args->getOption('format') === 'json';
        if (!$success) {
            $this->sendIntegrityAlert($reports, $io, !$jsonOutput);
        }
        if ($jsonOutput) {
            $io->out((string)json_encode(
                ['success' => $success, 'reports' => $reports],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
            ));

            return $success ? static::CODE_SUCCESS : static::CODE_ERROR;
        }

        foreach ($reports as $report) {
            $title = sprintf('%s : %d nœud(s)', $report['table'], $report['node_count']);
            if ($report['success'] === true) {
                $io->success($title . ' — arbre cohérent.');
                continue;
            }
            $io->error($title . sprintf(' — %d incohérence(s).', count($report['issues'])));
            foreach ($report['issues'] as $issue) {
                $io->out(sprintf('  [%s] %s', $issue['code'], $issue['message']));
                $io->out('    Détails : ' . (string)json_encode(
                    $issue['details'],
                    JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
                ));
            }
            $io->out('    Diagnostic ciblé : ' . $report['check_command']);
        }

        return $success ? static::CODE_SUCCESS : static::CODE_ERROR;
    }

    /**
     * Alerte l’opérateur configuré sans masquer l’échec du diagnostic initial.
     *
     * @param list<array<string, mixed>> $reports Rapports retournés par le contrôle.
     * @param \Cake\Console\ConsoleIo $io La sortie console.
     * @param bool $displayStatus Indique si le statut d’envoi doit être affiché.
     * @return void
     */
    private function sendIntegrityAlert(array $reports, ConsoleIo $io, bool $displayStatus): void
    {
        $alertService = new TreeIntegrityAlertService();
        $errors = $alertService->configurationErrors();
        if ($errors !== []) {
            if ($displayStatus) {
                foreach ($errors as $error) {
                    $io->error(__('Alerte non envoyée : {0}', $error));
                }
            }

            return;
        }

        $failedReports = array_values(array_filter(
            $reports,
            static fn(array $report): bool => $report['success'] === false,
        ));
        $recipient = (string)Configure::read('TreeIntegrity.alertRecipient', '');
        if (
            $alertService->send($failedReports)
        ) {
            if ($displayStatus) {
                $io->warning(__('Alerte d’intégrité envoyée à {0}.', $recipient));
            }

            return;
        }

        if ($displayStatus) {
            $io->error(__('Alerte non remise à {0}. Consultez logs/email.log.', $recipient));
        }
    }

    /**
     * Vérifie la configuration d’alerte sans ouvrir de connexion aux arbres.
     *
     * @param \Cake\Console\ConsoleIo $io La sortie console.
     * @return int Code de succès ou d’erreur.
     */
    private function checkAlertConfiguration(ConsoleIo $io): int
    {
        $errors = (new TreeIntegrityAlertService())->configurationErrors();
        if ($errors === []) {
            $io->success(__('Configuration des alertes TreeBehavior valide.'));

            return static::CODE_SUCCESS;
        }
        foreach ($errors as $error) {
            $io->error($error);
        }

        return static::CODE_ERROR;
    }
}
