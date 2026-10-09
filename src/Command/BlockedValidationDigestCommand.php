<?php
declare(strict_types=1);

namespace App\Command;

use App\Mailer\ValidationWorkflowMailer;
use App\Model\Entity\User;
use App\Service\Workflow\ApplicationformValidationWorkflow;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\CommandFactoryInterface;
use Cake\Console\ConsoleIo;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use Closure;
use RuntimeException;
use Throwable;

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
     * @param \Closure(): list<\App\Model\Entity\User>|null $administrators
     * @param \Closure(\App\Model\Entity\User): list<\App\Service\Workflow\BlockedValidationCycle>|null $findBlockedCycles
     * @param \Closure(\App\Model\Entity\User, list<\App\Service\Workflow\BlockedValidationCycle>): bool|null $sendDigest
     */
    public function __construct(
        ?CommandFactoryInterface $factory = null,
        ?Closure $administrators = null,
        ?Closure $findBlockedCycles = null,
        ?Closure $sendDigest = null,
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
            return (new ApplicationformValidationWorkflow())->findBlockedCycles($user);
        };
        $this->sendDigest = $sendDigest ?? static function (User $user, array $cycles): bool {
            return (new ValidationWorkflowMailer())->safeSend('blockedValidationDigest', [$user, $cycles]);
        };
    }

    /** Exécute la notification et retourne un code d'échec si un envoi échoue. */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        Log::write('info', 'Démarrage de validation notify-blocked', ['scope' => ['application']]);
        $io->out(__('Démarrage de la notification des cycles bloqués.'));
        $recipients = 0;
        $cyclesCount = 0;
        try {
            foreach (($this->administrators)() as $administrator) {
                $cycles = ($this->findBlockedCycles)($administrator);
                if ($cycles === []) {
                    continue;
                }
                $cyclesCount += count($cycles);
                if (!($this->sendDigest)($administrator, $cycles)) {
                    throw new RuntimeException(__('Échec de l’envoi à {0}.', $administrator->email));
                }
                $recipients++;
            }
            $message = __(
                'Notification terminée : {0} destinataire(s), {1} DAE traitée(s).',
                $recipients,
                $cyclesCount,
            );
            $io->success($message);
            Log::write('info', $message, ['scope' => ['application']]);

            return static::CODE_SUCCESS;
        } catch (Throwable $exception) {
            $io->error($exception->getMessage());
            Log::write(
                'error',
                'Échec de validation notify-blocked : ' . $exception->getMessage(),
                ['scope' => ['application']],
            );

            return static::CODE_ERROR;
        }
    }
}
