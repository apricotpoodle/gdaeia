<?php
declare(strict_types=1);

namespace App\Service\Workflow;

use App\Model\Entity\Applicationform;
use App\Model\Entity\Applicationvalidationstep;
use App\Model\Entity\User;
use App\Model\Entity\Validationsequence;
use App\Model\Entity\ValidationWorkflowRun;
use Cake\I18n\DateTime;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use RuntimeException;

/** Orchestration transactionnelle du cycle de validation d'une demande. */
final class ApplicationformValidationWorkflow
{
    private Table $runs;
    private Table $steps;
    private Table $sequences;
    private Table $roles;
    private Table $users;
    private Table $validations;
    private Table $settings;
    private Table $applicationforms;

    /** Initialise les tables utilisées par le workflow. */
    public function __construct()
    {
        $locator = TableRegistry::getTableLocator();
        $this->runs = $locator->get('ValidationWorkflowRuns');
        $this->steps = $locator->get('Applicationvalidationsteps');
        $this->sequences = $locator->get('Validationsequences');
        $this->roles = $locator->get('Roles');
        $this->users = $locator->get('Users');
        $this->validations = $locator->get('Validations');
        $this->settings = $locator->get('WorkflowSettings');
        $this->applicationforms = $locator->get('Applicationforms');
    }

    /** @return array{issues: list<string>, recipients: list<\App\Model\Entity\User>, run: object|null} */
    public function start(Applicationform $applicationform, User $actor): array
    {
        /** @var list<\App\Model\Entity\Validationsequence> $sequences */
        $sequences = $this->sequences->find()
            ->contain(['Roles'])
            ->where([
                'Validationsequences.department_id' => $applicationform->department_id,
                'Validationsequences.deleted IS' => null,
            ])
            ->orderByAsc('Validationsequences.sequence')->all()->toList();
        $issues = [];
        if ($sequences === []) {
            $issues[] = __('Aucune séquence de validation n’est configurée pour ce département.');
        }
        foreach ($sequences as $sequence) {
            if ($this->eligibleUsers($applicationform, (int)$sequence->role_id) === []) {
                $roleName = $sequence->role->name ?? __('Rôle n°{0}', $sequence->role_id);
                $issues[] = __(
                    'Le rôle « {0} » de la séquence {1} ne possède aucun utilisateur rattaché au département.',
                    $roleName,
                    $sequence->sequence,
                );
            }
        }
        if ($issues !== []) {
            return ['issues' => $issues, 'recipients' => [], 'run' => null];
        }

        $now = DateTime::now();
        $run = $this->runs->getConnection()->transactional(
            function () use ($applicationform, $actor, $sequences, $now): ValidationWorkflowRun {
                if ($this->runs->find()->where(['applicationform_id' => $applicationform->id])->count() !== 0) {
                    throw new RuntimeException(__('Le cycle de validation a déjà été lancé.'));
                }
                /** @var \App\Model\Entity\ValidationWorkflowRun $run */
                $run = $this->runs->newEntity([
                'applicationform_id' => $applicationform->id,
                'started_by_user_id' => $actor->id,
                'state' => ValidationWorkflowRun::STATE_PENDING,
                'started_at' => $now,
                ]);
                if (!$this->runs->save($run)) {
                    throw new WorkflowStartFailureException(__('Impossible de créer le cycle de validation.'));
                }
                $firstSequence = min(array_map(
                    static fn(Validationsequence $item): int => $item->sequence,
                    $sequences,
                ));
                foreach ($sequences as $sequence) {
                    $active = (int)$sequence->sequence === $firstSequence;
                    /** @var \App\Model\Entity\Applicationvalidationstep $step */
                    $step = $this->steps->newEntity([
                    'applicationform_id' => $applicationform->id,
                    'validation_workflow_run_id' => $run->id,
                    'validationsequence_id' => $sequence->id,
                    'role_id' => $sequence->role_id,
                    'sequence_number' => $sequence->sequence,
                    'validationstatus_id' => null,
                    'state' => $active ? 'en_attente' : 'a_venir',
                    'activated_at' => $active ? $now : null,
                    'due_at' => $active ? $now->addHours($this->delayFor($sequence)) : null,
                    ]);
                    if (!$this->steps->save($step)) {
                        throw new WorkflowStartFailureException(__('Impossible de créer les étapes de validation.'));
                    }
                }

                return $run;
            },
        );

        return [
            'issues' => [],
            'recipients' => $this->recipientsForCurrentRun($run),
            'run' => $run,
        ];
    }

    /**
     * Supprime intégralement le cycle lancé pour restituer la demande à son état antérieur.
     *
     * Les validations historiques qui ne sont rattachées à aucune étape de ce
     * cycle ne sont pas concernées. L'ordre de suppression respecte les clés
     * étrangères : votes, étapes, puis exécution.
     *
     * @param \App\Model\Entity\Applicationform $applicationform Demande dont le cycle doit être supprimé.
     * @return array{run_id: int, validations: int, steps: int, runs: int} Lignes supprimées par table.
     */
    public function reset(Applicationform $applicationform): array
    {
        return $this->runs->getConnection()->transactional(function () use ($applicationform): array {
            return $this->purgeRun($applicationform, true);
        });
    }

    /**
     * Supprime une demande et, si nécessaire, son cycle dans une même transaction.
     *
     * @param \App\Model\Entity\Applicationform $applicationform Demande à supprimer.
     * @return array{validations: int, steps: int, runs: int} Lignes de workflow supprimées.
     */
    public function deleteApplicationform(Applicationform $applicationform): array
    {
        return $this->runs->getConnection()->transactional(function () use ($applicationform): array {
            $deleted = $this->purgeRun($applicationform, false);
            $deleted['validations'] += $this->validations->deleteAll([
                'applicationform_id' => $applicationform->id,
            ]);
            $deleted['steps'] += $this->steps->deleteAll([
                'applicationform_id' => $applicationform->id,
            ]);
            if (!$this->applicationforms->delete($applicationform)) {
                throw new RuntimeException(__('Impossible de supprimer la demande de recrutement.'));
            }

            unset($deleted['run_id']);

            return $deleted;
        });
    }

    /**
     * Purge les données exclusivement rattachées à l'exécution de la demande.
     *
     * @return array{run_id: int|null, validations: int, steps: int, runs: int}
     */
    private function purgeRun(Applicationform $applicationform, bool $required): array
    {
        /** @var \App\Model\Entity\ValidationWorkflowRun|null $run */
        $run = $this->runs->find()
            ->select(['id'])
            ->where(['applicationform_id' => $applicationform->id])
            ->first();
        if ($run === null) {
            if ($required) {
                throw new RuntimeException(__('Aucun cycle de validation ne peut être remis à zéro.'));
            }

            return ['run_id' => null, 'validations' => 0, 'steps' => 0, 'runs' => 0];
        }

        $stepIds = $this->steps->find()
            ->select(['id'])
            ->where(['validation_workflow_run_id' => $run->id])
            ->all()
            ->extract('id')
            ->toList();
        $deletedValidations = $stepIds === []
            ? 0
            : $this->validations->deleteAll(['applicationvalidationstep_id IN' => $stepIds]);
        $deletedSteps = $this->steps->deleteAll(['validation_workflow_run_id' => $run->id]);
        $deletedRuns = $this->runs->deleteAll(['id' => $run->id]);

        return [
            'run_id' => (int)$run->id,
            'validations' => $deletedValidations,
            'steps' => $deletedSteps,
            'runs' => $deletedRuns,
        ];
    }

    /** @return array{state: string, nextRecipients: list<\App\Model\Entity\User>, final: bool} */
    public function vote(
        Applicationform $applicationform,
        User $actor,
        int $stepId,
        bool $approved,
        string $comment,
        bool $isProxy,
    ): array {
        return $this->runs->getConnection()->transactional(function () use (
            $applicationform,
            $actor,
            $stepId,
            $approved,
            $comment,
            $isProxy,
        ) {
            /** @var \App\Model\Entity\ValidationWorkflowRun|null $run */
            $run = $this->runs->find()->where([
                'applicationform_id' => $applicationform->id,
                'state' => ValidationWorkflowRun::STATE_PENDING,
            ])->first();
            if ($run === null) {
                throw new RuntimeException(__('Aucun cycle actif ne permet ce vote.'));
            }
            /** @var \App\Model\Entity\Applicationvalidationstep|null $step */
            $step = $this->steps->find()->where([
                'id' => $stepId,
                'validation_workflow_run_id' => $run->id,
                'state' => 'en_attente',
            ])->first();
            if (
                $step === null || !$this->canVoteStep(
                    $step,
                    $applicationform,
                    $actor,
                    $isProxy,
                )
            ) {
                throw new RuntimeException(__('Vous n’êtes pas autorisé à voter pour cette étape.'));
            }
            if (!$approved && trim($comment) === '') {
                throw new RuntimeException(__('Un commentaire est obligatoire lors d’un refus.'));
            }
            $step->state = $approved ? 'acceptee' : 'refusee';
            $step->validationstatus_id = $approved ? 3 : 5;
            $step->completed_at = DateTime::now();
            $step->comment = trim($comment) ?: null;
            if (!$this->steps->save($step)) {
                throw new RuntimeException(__('Impossible d’enregistrer le vote.'));
            }
            $validation = $this->validations->newEntity([
                'applicationform_id' => $applicationform->id, 'applicationvalidationstep_id' => $step->id,
                'user_id' => $actor->id, 'role_id' => $step->role_id, 'validated' => DateTime::now(),
                'validationstatus_id' => $approved ? 3 : 5,
                'obs' => trim($comment) ?: null, 'is_proxy' => $isProxy,
            ]);
            if (!$this->validations->save($validation)) {
                throw new RuntimeException(__('Impossible de tracer le vote.'));
            }
            if (!$approved) {
                $run->state = ValidationWorkflowRun::STATE_REJECTED;
                $run->finished_at = DateTime::now();
                $this->runs->saveOrFail($run);
                $this->steps->updateAll(['state' => 'annulee'], [
                    'validation_workflow_run_id' => $run->id,
                    'state IN' => ['en_attente', 'a_venir'],
                ]);

                return ['state' => $run->state, 'nextRecipients' => [], 'final' => true];
            }
            $sequence = (int)$step->sequence_number;
            if (
                $this->steps->find()->where([
                'validation_workflow_run_id' => $run->id,
                'sequence_number' => $sequence,
                'state' => 'en_attente',
                ])->count() > 0
            ) {
                return ['state' => $run->state, 'nextRecipients' => [], 'final' => false];
            }
            /** @var \App\Model\Entity\Applicationvalidationstep|null $next */
            $next = $this->steps->find()->where([
                'validation_workflow_run_id' => $run->id,
                'state' => 'a_venir',
            ])->orderByAsc('sequence_number')->first();
            if ($next === null) {
                $run->state = ValidationWorkflowRun::STATE_ACCEPTED;
                $run->finished_at = DateTime::now();
                $this->runs->saveOrFail($run);

                return ['state' => $run->state, 'nextRecipients' => [], 'final' => true];
            }
            /** @var \App\Model\Entity\Validationsequence $nextSequence */
            $nextSequence = $this->sequences->get($next->validationsequence_id);
            $dueAt = DateTime::now()->addHours($this->delayFor($nextSequence));
            $this->steps->updateAll([
                'state' => 'en_attente',
                'activated_at' => DateTime::now(),
                'due_at' => $dueAt,
            ], [
                'validation_workflow_run_id' => $run->id,
                'sequence_number' => $next->sequence_number,
                'state' => 'a_venir',
            ]);

            return ['state' => $run->state, 'nextRecipients' => $this->recipientsForCurrentRun($run), 'final' => false];
        });
    }

    /** Vérifie l'éligibilité d'un utilisateur à voter pour une étape active. */
    public function canVoteStep(
        Applicationvalidationstep $step,
        Applicationform $applicationform,
        User $user,
        bool $asProxy,
    ): bool {
        if ($asProxy) {
            return (int)$user->get('role_id') === 1 && $step->due_at !== null && $step->due_at <= DateTime::now();
        }
        foreach ($this->eligibleUsers($applicationform, (int)$step->role_id) as $eligible) {
            if ((int)$eligible->id === (int)$user->id) {
                return true;
            }
        }

        return false;
    }

    /** Vérifie l'éligibilité d'édition sur une étape effectivement active. */
    public function canEditDuringActiveStep(Applicationform $applicationform, User $user): bool
    {
        /** @var \App\Model\Entity\ValidationWorkflowRun|null $run */
        $run = $this->runs->find()->where([
            'applicationform_id' => $applicationform->id,
            'state' => ValidationWorkflowRun::STATE_PENDING,
        ])->first();
        if ($run === null) {
            return false;
        }

        /** @var iterable<\App\Model\Entity\Applicationvalidationstep> $steps */
        $steps = $this->steps->find()->where([
            'validation_workflow_run_id' => $run->id,
            'state' => 'en_attente',
        ])->all();
        foreach ($steps as $step) {
            if ($this->canVoteStep($step, $applicationform, $user, (int)$user->role_id === User::ROLE_ADMIN)) {
                return true;
            }
        }

        return false;
    }

    /** @return list<\App\Model\Entity\User> */
    public function recipientsForCurrentRun(ValidationWorkflowRun $run): array
    {
        /** @var iterable<\App\Model\Entity\Applicationvalidationstep> $steps */
        $steps = $this->steps->find()->contain(['Applicationforms'])->where([
            'validation_workflow_run_id' => $run->id,
            'state' => 'en_attente',
        ])->all();
        $recipients = [];
        foreach ($steps as $step) {
            foreach ($this->eligibleUsers($step->applicationform, (int)$step->role_id) as $user) {
                $recipients[(int)$user->id] = $user;
            }
        }

        return array_values($recipients);
    }

    /** @return list<array{applicationform: \App\Model\Entity\Applicationform, recipients: list<\App\Model\Entity\User>}> */
    public function collectDueReminders(): array
    {
        $now = DateTime::now();
        /** @var list<\App\Model\Entity\Applicationvalidationstep> $steps */
        $steps = $this->steps->find()->contain(['Applicationforms', 'ValidationWorkflowRuns'])
            ->where([
                'Applicationvalidationsteps.state' => 'en_attente',
                'Applicationvalidationsteps.due_at <=' => $now,
            ])
            ->all()->toList();
        $result = [];
        foreach ($steps as $step) {
            if ($step->last_reminded_at !== null && $step->last_reminded_at > $now->subDays(1)) {
                continue;
            }
            $recipients = $this->eligibleUsers($step->applicationform, (int)$step->role_id);
            if ($recipients === []) {
                continue;
            }
            $step->last_reminded_at = $now;
            $step->reminder_count = (int)$step->reminder_count + 1;
            $this->steps->saveOrFail($step);
            $result[] = ['applicationform' => $step->applicationform, 'recipients' => $recipients];
        }

        return $result;
    }

    /** @return list<\App\Model\Entity\User> */
    private function eligibleUsers(Applicationform $applicationform, int $roleId): array
    {
        if ($this->isRequesterRole($roleId)) {
            /** @var \App\Model\Entity\User|null $requester */
            $requester = $this->users->find()
                ->where(['Users.id' => $applicationform->user_id, 'Users.deleted IS' => null])
                ->first();

            return $requester === null ? [] : [$requester];
        }

        /** @var list<\App\Model\Entity\User> $users */
        $users = $this->users->find()->innerJoinWith('UserDepartments', function ($query) use ($applicationform) {
            return $query->where(['UserDepartments.department_id' => $applicationform->department_id]);
        })->where(['Users.role_id' => $roleId, 'Users.deleted IS' => null])->all()->toList();

        return $users;
    }

    /** Détermine le rôle sémantique du créateur de la demande par son code stable. */
    private function isRequesterRole(int $roleId): bool
    {
        /** @var \App\Model\Entity\Role|null $role */
        $role = $this->roles->find()
            ->select(['id', 'code'])
            ->where(['Roles.id' => $roleId])
            ->first();

        return $role !== null && $role->code === 'dem';
    }

    /** Retourne le délai d'échéance de la séquence, avec repli global. */
    private function delayFor(Validationsequence $sequence): int
    {
        if ($sequence->reminder_delay_hours !== null) {
            return max(1, (int)$sequence->reminder_delay_hours);
        }
        $setting = $this->settings->find()->where(['name' => 'validation.default_due_hours'])->first();

        return max(1, (int)($setting->value ?? 72));
    }
}
