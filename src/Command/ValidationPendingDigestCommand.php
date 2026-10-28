<?php
declare(strict_types=1);

namespace App\Command;

use App\Mailer\ValidationWorkflowMailer;
use App\Service\Workflow\ApplicationformValidationWorkflow;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\CommandFactoryInterface;
use Cake\Console\ConsoleIo;
use Cake\Database\Exception\DatabaseException;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use Closure;

/** Envoie la liste quotidienne des demandes nécessitant une validation. */
final class ValidationPendingDigestCommand extends Command
{
    /**
     * @var \Closure(): list<array{recipient: \App\Model\Entity\User, applicationforms: list<\App\Model\Entity\Applicationform>}>
     */
    private readonly Closure $collect;
    /**
     * @var \Closure(\App\Model\Entity\User, list<\App\Model\Entity\Applicationform>): bool
     */
    private readonly Closure $send;

    /** Initialise les collecteurs et l'expéditeur injectables. */
    public function __construct(
        ?CommandFactoryInterface $factory = null,
        ?Closure $collect = null,
        ?Closure $send = null,
    ) {
        parent::__construct($factory);
        $this->collect = $collect ?? static function (): array {
            return (new ApplicationformValidationWorkflow())->collectPendingDigest();
        };
        $this->send = $send ?? static fn($recipient, $forms): bool => (new ValidationWorkflowMailer())
            ->safeSend('pendingValidationDigest', [$recipient, $forms]);
    }

    /** Exécute la synthèse quotidienne. */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $settings = TableRegistry::getTableLocator()->get('WorkflowSettings');
        $setting = null;
        try {
            $setting = $settings->find()->where(['name' => 'notification.pending.last_run'])->first();
        } catch (DatabaseException) {
            // Les environnements antérieurs à V3 n'ont pas encore le paramétrage.
        }
        if ($setting !== null && (string)$setting->get('value') === DateTime::now()->format('Y-m-d')) {
            $io->success(__('La synthèse quotidienne a déjà été envoyée aujourd’hui.'));

            return static::CODE_SUCCESS;
        }
        $sent = 0;
        foreach (($this->collect)() as $digest) {
            if (($this->send)($digest['recipient'], $digest['applicationforms'])) {
                $sent++;
            }
        }
        if ($setting === null) {
            $setting = $settings->newEntity(['name' => 'notification.pending.last_run']);
        }
        $setting->set('value', DateTime::now()->format('Y-m-d'));
        $settings->save($setting);
        $io->success(__('{0} synthèse(s) quotidienne(s) envoyée(s).', $sent));

        return static::CODE_SUCCESS;
    }
}
