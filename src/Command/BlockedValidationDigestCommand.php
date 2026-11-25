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
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use Closure;

/** Envoie le récapitulatif quotidien des cycles de validation bloqués. */
final class BlockedValidationDigestCommand extends Command
{
    /**
     * @var \Closure(): list<\App\Model\Entity\User>
     */
    private readonly Closure $administrators;
    /**
     * @var \Closure(\App\Model\Entity\User): list<\App\Service\Workflow\BlockedValidationCycle>
     */
    private readonly Closure $findBlockedCycles;
    /**
     * @var \Closure(\App\Model\Entity\User, list<\App\Service\Workflow\BlockedValidationCycle>): bool
     */
    private readonly Closure $sendDigest;

    /**
     * @var \Closure(string, string, \App\Model\Entity\User, \Closure): bool
     */
    private readonly Closure $sendOnce;

    /**
     * @param \Closure(): list<\App\Model\Entity\User>|null $administrators
     * @param \Closure(\App\Model\Entity\User): list<\App\Service\Workflow\BlockedValidationCycle>|null $findBlockedCycles
     * @param \Closure(\App\Model\Entity\User, list<\App\Service\Workflow\BlockedValidationCycle>): bool|null $sendDigest
     * @param \Closure(string, string, \App\Model\Entity\User, \Closure): bool|null $sendOnce
     */
    public function __construct(
        ?CommandFactoryInterface $factory = null,
        ?Closure $administrators = null,
        ?Closure $findBlockedCycles = null,
        ?Closure $sendDigest = null,
        ?Closure $sendOnce = null,
    ) {
        parent::__construct($factory);
        $this->administrators = $administrators ?? static function (): array {
            /** @var list<\App\Model\Entity\User> $users */
            $users = TableRegistry::getTableLocator()->get('Users')->find()->where([
                'Users.deleted IS' => null,
                'OR' => ['Users.role_id' => User::ROLE_ADMIN, 'Users.issuperuser' => true],
            ])->all()->toList();

            return $users;
        };
        $this->findBlockedCycles = $findBlockedCycles ?? static function (User $user): array {
            return (new ApplicationformValidationWorkflow())->findBlockedCycles($user, true);
        };
        $this->sendDigest = $sendDigest ?? static function (User $user, array $cycles): bool {
            return (new ValidationWorkflowMailer())->safeSend('blockedValidationDigest', [$user, $cycles]);
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

    /** Exécute la notification et retourne un code d'échec si un envoi échoue. */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $day = DateTime::now()->setTimezone('Europe/Paris')->format('Y-m-d');
        Log::write('info', 'Démarrage de validation notify-blocked', ['scope' => ['application']]);
        $io->out(__('Démarrage de la notification des cycles bloqués.'));
        $recipients = 0;
        $failed = 0;
        $withoutAddress = 0;
        $cyclesCount = 0;
        foreach (($this->administrators)() as $administrator) {
            $cycles = ($this->findBlockedCycles)($administrator);
            if ($cycles === []) {
                continue;
            }
            $cyclesCount += count($cycles);
            $email = $administrator->get(User::FIELD_EMAIL);
            if (!is_string($email) || trim($email) === '') {
                $withoutAddress++;
                continue;
            }
            $sent = ($this->sendOnce)(
                'blocked_digest',
                $day,
                $administrator,
                fn(): bool => ($this->sendDigest)($administrator, $cycles),
            );
            if ($sent) {
                $recipients++;
            } else {
                $failed++;
            }
        }
        $summary = __(
            'Notification terminée : {0} réussite(s), {1} échec(s), '
            . '{2} destinataire(s) sans adresse, {3} DAE traitée(s).',
            $recipients,
            $failed,
            $withoutAddress,
            $cyclesCount,
        );
        if ($failed > 0) {
            $message = __('Échec de notification : {0}', $summary);
            $io->error($message);
            Log::write(
                'error',
                'Échec de validation notify-blocked : ' . $summary,
                ['scope' => ['application']],
            );

            return static::CODE_ERROR;
        }
        $io->success($summary);
        Log::write('info', $summary, ['scope' => ['application']]);

        return static::CODE_SUCCESS;
    }
}
