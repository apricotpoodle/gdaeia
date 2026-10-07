<?php
declare(strict_types=1);

namespace App\Command;

use App\Mailer\ValidationWorkflowMailer;
use App\Model\Entity\Applicationform;
use App\Model\Entity\User;
use App\Service\Workflow\ApplicationformValidationWorkflow;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\CommandFactoryInterface;
use Cake\Console\ConsoleIo;
use Closure;

/** Envoie, au plus une fois par jour, les relances des étapes échues. */
final class ValidationReminderCommand extends Command
{
    /**
     * @var \Closure(): list<array{applicationform: \App\Model\Entity\Applicationform, recipients: list<\App\Model\Entity\User>}>
     */
    private readonly Closure $collectDueReminders;

    /**
     * @var \Closure(\App\Model\Entity\User, \App\Model\Entity\Applicationform): bool
     */
    private readonly Closure $sendReminder;

    /**
     * @param \Cake\Console\CommandFactoryInterface|null $factory Fabrique CakePHP.
     * @param \Closure(): list<array{applicationform: \App\Model\Entity\Applicationform, recipients: list<\App\Model\Entity\User>}>|null $collectDueReminders
     *   Collecte injectable.
     * @param \Closure(\App\Model\Entity\User, \App\Model\Entity\Applicationform): bool|null $sendReminder Expéditeur injectable.
     */
    public function __construct(
        ?CommandFactoryInterface $factory = null,
        ?Closure $collectDueReminders = null,
        ?Closure $sendReminder = null,
    ) {
        parent::__construct($factory);
        $this->collectDueReminders = $collectDueReminders ?? static function (): array {
            return (new ApplicationformValidationWorkflow())->collectDueReminders();
        };
        $this->sendReminder = $sendReminder ?? static function (
            User $recipient,
            Applicationform $applicationform,
        ): bool {
            return (new ValidationWorkflowMailer())->safeSend('validationStep', [$recipient, $applicationform]);
        };
    }

    /**
     * Envoie les relances des étapes de validation échues.
     *
     * @param \Cake\Console\Arguments $args Arguments de la commande.
     * @param \Cake\Console\ConsoleIo $io Sortie de la console.
     * @return int Code de sortie de la commande.
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $reminders = ($this->collectDueReminders)();
        foreach ($reminders as $reminder) {
            foreach ($reminder['recipients'] as $recipient) {
                ($this->sendReminder)($recipient, $reminder['applicationform']);
            }
        }
        $io->success(__('{0} relance(s) envoyée(s).', count($reminders)));

        return static::CODE_SUCCESS;
    }
}
