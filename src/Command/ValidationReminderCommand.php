<?php
declare(strict_types=1);

namespace App\Command;

use App\Mailer\ValidationWorkflowMailer;
use App\Service\Workflow\ApplicationformValidationWorkflow;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;

/** Envoie, au plus une fois par jour, les relances des étapes échues. */
final class ValidationReminderCommand extends Command
{
    /**
     * Envoie les relances des étapes de validation échues.
     *
     * @param \Cake\Console\Arguments $args Arguments de la commande.
     * @param \Cake\Console\ConsoleIo $io Sortie de la console.
     * @return int Code de sortie de la commande.
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $reminders = (new ApplicationformValidationWorkflow())->collectDueReminders();
        foreach ($reminders as $reminder) {
            foreach ($reminder['recipients'] as $recipient) {
                (new ValidationWorkflowMailer())->safeSend(
                    'validationStep',
                    [$recipient, $reminder['applicationform']],
                );
            }
        }
        $io->success(__('{0} relance(s) envoyée(s).', count($reminders)));

        return static::CODE_SUCCESS;
    }
}
