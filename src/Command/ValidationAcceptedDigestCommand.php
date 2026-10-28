<?php
declare(strict_types=1);

namespace App\Command;

use App\Mailer\ValidationWorkflowMailer;
use App\Model\Entity\User;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\CommandFactoryInterface;
use Cake\Console\ConsoleIo;
use Cake\Database\Exception\DatabaseException;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;

/** Envoie la synthèse hebdomadaire des demandes entièrement validées. */
final class ValidationAcceptedDigestCommand extends Command
{
    /** Initialise la commande CakePHP. */
    public function __construct(?CommandFactoryInterface $factory = null)
    {
        parent::__construct($factory);
    }

    /** Exécute la synthèse des demandes acceptées. */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $settings = TableRegistry::getTableLocator()->get('WorkflowSettings');
        try {
            $lastRun = $settings->find()->where(['name' => 'notification.accepted.last_run'])->first();
        } catch (DatabaseException) {
            $lastRun = null;
        }
        $week = DateTime::now()->format('o-W');
        if ($lastRun !== null && (string)$lastRun->get('value') === $week) {
            $io->success(__('La synthèse hebdomadaire a déjà été envoyée cette semaine.'));

            return static::CODE_SUCCESS;
        }
        $runs = TableRegistry::getTableLocator()->get('ValidationWorkflowRuns')->find()
            ->contain(['Applicationforms'])
            ->where([
                'ValidationWorkflowRuns.state' => 'acceptee',
                'ValidationWorkflowRuns.finished_at >=' => DateTime::now()->subDays(7),
            ])
            ->orderByAsc('ValidationWorkflowRuns.finished_at')->all();
        $forms = [];
        foreach ($runs as $run) {
            /** @var \App\Model\Entity\Applicationform|null $form */
            $form = $run->get('applicationform');
            if ($form !== null) {
                $forms[(int)$form->id] = $form;
            }
        }
        if ($forms === []) {
            $io->success(__('Aucune demande complètement validée cette semaine.'));

            return static::CODE_SUCCESS;
        }
        $recipients = TableRegistry::getTableLocator()->get('Users')->find()->where([
            'Users.deleted IS' => null,
            'OR' => ['Users.issuperuser' => true, 'Users.role_id' => User::ROLE_ADMIN],
        ])->all();
        /** @var list<\App\Model\Entity\Applicationform> $formList */
        $formList = array_values($forms);
        $sent = 0;
        foreach ($recipients as $recipient) {
            if (
                (new ValidationWorkflowMailer())->safeSend(
                    'acceptedValidationDigest',
                    [$recipient, $formList],
                )
            ) {
                $sent++;
            }
        }
        $lastRun ??= $settings->newEntity(['name' => 'notification.accepted.last_run']);
        $lastRun->set('value', $week);
        $settings->save($lastRun);
        $io->success(__('{0} synthèse(s) hebdomadaire(s) envoyée(s).', $sent));

        return static::CODE_SUCCESS;
    }
}
