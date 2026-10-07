<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\TreeIntegrityAlertService;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\CommandFactoryInterface;
use Cake\Console\ConsoleIo;
use Closure;

/** Envoie une alerte TreeBehavior de test sans contrôler ni modifier les données. */
final class TreeIntegrityAlertTestCommand extends Command
{
    /**
     * @var \Closure(): list<string>
     */
    private readonly Closure $configurationErrors;

    /**
     * @var \Closure(): bool
     */
    private readonly Closure $sendTest;

    /**
     * @param \Cake\Console\CommandFactoryInterface|null $factory Fabrique CakePHP.
     * @param \Closure(): list<string>|null $configurationErrors Validateur injectable.
     * @param \Closure(): bool|null $sendTest Expéditeur injectable.
     */
    public function __construct(
        ?CommandFactoryInterface $factory = null,
        ?Closure $configurationErrors = null,
        ?Closure $sendTest = null,
    ) {
        parent::__construct($factory);
        $this->configurationErrors = $configurationErrors ?? static function (): array {
            return (new TreeIntegrityAlertService())->configurationErrors();
        };
        $this->sendTest = $sendTest ?? static function (): bool {
            return (new TreeIntegrityAlertService())->sendTest();
        };
    }

    /**
     * Vérifie la configuration puis envoie un courriel explicitement marqué comme test.
     *
     * @param \Cake\Console\Arguments $args Arguments de la commande.
     * @param \Cake\Console\ConsoleIo $io La sortie console.
     * @return int Code de succès ou d’erreur.
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $errors = ($this->configurationErrors)();
        if ($errors !== []) {
            foreach ($errors as $error) {
                $io->error($error);
            }

            return static::CODE_ERROR;
        }
        if (!($this->sendTest)()) {
            $io->error(__('Alerte de test non remise. Consultez logs/email.log.'));

            return static::CODE_ERROR;
        }

        $io->success(__('Alerte de test envoyée à l’opérateur configuré.'));

        return static::CODE_SUCCESS;
    }
}
