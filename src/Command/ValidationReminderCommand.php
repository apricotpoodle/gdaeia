<?php
declare(strict_types=1);

namespace App\Command;

use App\Mailer\ValidationWorkflowMailer;
use App\Model\Entity\Applicationform;
use App\Model\Entity\User;
use App\Service\NotificationDeliveryService;
use App\Service\Workflow\ApplicationformValidationWorkflow;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\CommandFactoryInterface;
use Cake\Console\ConsoleIo;
use Cake\ORM\TableRegistry;
use Closure;

/** Envoie, au plus une fois par jour, les relances des étapes échues. */
final class ValidationReminderCommand extends Command
{
    /**
     * @var \Closure(): list<array{step_id: int, reminder_count: int, applicationform: \App\Model\Entity\Applicationform, recipients: list<\App\Model\Entity\User>}>
     */
    private readonly Closure $collectDueReminders;

    /**
     * @var \Closure(\App\Model\Entity\User, \App\Model\Entity\Applicationform): bool
     */
    private readonly Closure $sendReminder;

    /**
     * @var \Closure(int, int): bool
     */
    private readonly Closure $markReminderSent;

    /**
     * @var \Closure(string, string, \App\Model\Entity\User, \Closure): bool
     */
    private readonly Closure $sendOnce;

    /**
     * @param \Cake\Console\CommandFactoryInterface|null $factory Fabrique CakePHP.
     * @param \Closure(): list<array{step_id: int, reminder_count: int, applicationform: \App\Model\Entity\Applicationform, recipients: list<\App\Model\Entity\User>}>|null $collectDueReminders
     *   Collecte injectable.
     * @param \Closure(\App\Model\Entity\User, \App\Model\Entity\Applicationform): bool|null $sendReminder Expéditeur injectable.
     * @param \Closure(int, int): bool|null $markReminderSent Persistance après les envois.
     * @param \Closure(string, string, \App\Model\Entity\User, \Closure): bool|null $sendOnce Livraison idempotente injectable.
     */
    public function __construct(
        ?CommandFactoryInterface $factory = null,
        ?Closure $collectDueReminders = null,
        ?Closure $sendReminder = null,
        ?Closure $markReminderSent = null,
        ?Closure $sendOnce = null,
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
        $this->markReminderSent = $markReminderSent ?? static function (int $stepId, int $reminderCount): bool {
            return (new ApplicationformValidationWorkflow())->markReminderSent($stepId, $reminderCount);
        };
        $this->sendOnce = $sendOnce ?? static function (
            string $type,
            string $eventKey,
            User $recipient,
            Closure $send,
        ): bool {
            /** @var \App\Model\Table\NotificationDeliveriesTable $deliveries */
            $deliveries = TableRegistry::getTableLocator()->get('NotificationDeliveries');

            return (new NotificationDeliveryService($deliveries))->sendOnce($type, $eventKey, $recipient, $send);
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
        $sent = 0;
        $failed = 0;
        $withoutRecipients = 0;
        foreach ($reminders as $reminder) {
            $stepId = $reminder['step_id'];
            $nextReminderCount = $reminder['reminder_count'] + 1;
            if ($reminder['recipients'] === []) {
                $withoutRecipients++;
                continue;
            }

            $allSent = true;
            foreach ($reminder['recipients'] as $recipient) {
                $email = $recipient->get(User::FIELD_EMAIL);
                if (!is_string($email) || trim($email) === '') {
                    $withoutRecipients++;
                    $allSent = false;
                    continue;
                }
                $eventKey = sprintf('step:%d:reminder:%d', $stepId, $nextReminderCount);
                $sentSuccessfully = ($this->sendOnce)(
                    'validation_reminder',
                    $eventKey,
                    $recipient,
                    fn(): bool => ($this->sendReminder)($recipient, $reminder['applicationform']),
                );
                if ($sentSuccessfully) {
                    $sent++;
                } else {
                    $failed++;
                    $allSent = false;
                }
            }
            if ($allSent && !($this->markReminderSent)($stepId, $nextReminderCount)) {
                $failed++;
            }
        }
        $message = __(
            'Relances terminées : {0} réussite(s), {1} échec(s), {2} destinataire(s) sans adresse.',
            $sent,
            $failed,
            $withoutRecipients,
        );
        if ($failed > 0) {
            $io->error($message);

            return static::CODE_ERROR;
        }
        $io->success($message);

        return static::CODE_SUCCESS;
    }
}
