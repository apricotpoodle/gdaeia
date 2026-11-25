<?php
declare(strict_types=1);

namespace App\Command;

use App\Mailer\ValidationWorkflowMailer;
use App\Model\Entity\User;
use App\Service\NotificationDeliveryService;
use App\Service\Workflow\ApplicationformValidationWorkflow;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\CommandFactoryInterface;
use Cake\Console\ConsoleIo;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use Closure;

/** Envoie la synthèse hebdomadaire des demandes entièrement validées. */
final class ValidationAcceptedDigestCommand extends Command
{
    /**
     * @var \Closure(\Cake\I18n\DateTime, \Cake\I18n\DateTime): list<array{recipient: \App\Model\Entity\User, applicationforms: list<\App\Model\Entity\Applicationform>}>
     */
    private readonly Closure $collect;

    /**
     * @var \Closure(\App\Model\Entity\User, list<\App\Model\Entity\Applicationform>): bool
     */
    private readonly Closure $send;

    /**
     * @var \Closure(string, string, \App\Model\Entity\User, \Closure): bool
     */
    private readonly Closure $sendOnce;

    /** Initialise les collecteurs et l'expéditeur injectables. */
    public function __construct(
        ?CommandFactoryInterface $factory = null,
        ?Closure $collect = null,
        ?Closure $send = null,
        ?Closure $sendOnce = null,
    ) {
        parent::__construct($factory);
        $this->collect = $collect ?? static function (DateTime $from, DateTime $to): array {
            return (new ApplicationformValidationWorkflow())->collectAcceptedDigest($from, $to);
        };
        $this->send = $send ?? static fn($recipient, $forms): bool => (new ValidationWorkflowMailer())
            ->safeSend('acceptedValidationDigest', [$recipient, $forms]);
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

    /** Exécute la synthèse des demandes acceptées pendant la semaine ISO. */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $now = DateTime::now()->setTimezone('Europe/Paris');
        $from = $now->subDays((int)$now->format('N') - 1)->setTime(0, 0);
        $to = $from->addDays(7);
        $week = $from->format('o-W');
        $sent = 0;
        $failed = 0;
        $withoutAddress = 0;
        foreach (($this->collect)($from, $to) as $digest) {
            $recipient = $digest['recipient'];
            $email = $recipient->get(User::FIELD_EMAIL);
            if (!is_string($email) || trim($email) === '') {
                $withoutAddress++;
                continue;
            }
            $success = ($this->sendOnce)(
                'accepted_digest',
                $week,
                $recipient,
                fn(): bool => ($this->send)($recipient, $digest['applicationforms']),
            );
            if ($success) {
                $sent++;
            } else {
                $failed++;
            }
        }
        $message = __(
            'Synthèse hebdomadaire : {0} réussite(s), {1} échec(s), {2} destinataire(s) sans adresse.',
            $sent,
            $failed,
            $withoutAddress,
        );
        if ($failed > 0) {
            $io->error($message);

            return static::CODE_ERROR;
        }
        $io->success($message);

        return static::CODE_SUCCESS;
    }
}
