<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use App\Mailer\ValidationWorkflowMailer;
use App\Model\Entity\Applicationform;
use App\Model\Entity\Applicationvalidationstep;
use App\Model\Entity\Period;
use App\Model\Entity\Role;
use App\Model\Entity\User;
use App\Model\Entity\ValidationCommentTemplate;
use App\Model\Entity\ValidationWorkflowRun;
use App\Service\CgrResolverService;
use App\Service\DataGrid\TabulatorAdapter;
use App\Service\SalaryCalculationService;
use App\Service\Security\FieldAuthorizationService;
use App\Service\Workflow\ApplicationformValidationWorkflow;
use App\Service\Workflow\BlockedValidationCycle;
use App\Service\Workflow\BlockedValidationStep;
use App\Service\Workflow\WorkflowStartFailureException;
use App\Service\WorkingDaysService;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Exception\UnprocessableContentException;
use Cake\Http\Response;
use Cake\I18n\DateTime;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\TableRegistry;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use RuntimeException;

/**
 * Class ApplicationformsController (API)
 *
 * Expose les données des demandes sous format JSON pour le front-end.
 *
 * @property \App\Model\Table\ApplicationformsTable $Applicationforms
 */
class ApplicationformsController extends AppController
{
    /**
     * Démarre le cycle de validation d'une demande.
     *
     * @param string $id Identifiant de la demande.
     * @return \Cake\Http\Response Réponse JSON.
     */
    public function startValidation(string $id): Response
    {
        $this->request->allowMethod(['post']);
        $applicationform = $this->Applicationforms->get($id);
        $this->Authorization->authorize($applicationform, 'launchValidation');
        /** @var \App\Model\Entity\User $actor */
        $actor = $this->request->getAttribute('identity')->getOriginalData();
        try {
            $result = (new ApplicationformValidationWorkflow())->start($applicationform, $actor);
            if ($result['issues'] !== []) {
                $this->sendBlockedStartAlert($applicationform, $result['issues']);

                return $this->workflowResponse(
                    false,
                    __('Le cycle ne peut pas être lancé.'),
                    $result['issues'],
                    422,
                );
            }
            foreach ($result['recipients'] as $recipient) {
                (new ValidationWorkflowMailer())->safeSend('validationStep', [$recipient, $applicationform]);
            }

            return $this->workflowResponse(true, __('Le cycle de validation est lancé.'), []);
        } catch (WorkflowStartFailureException $exception) {
            $this->sendBlockedStartAlert($applicationform, [$exception->getMessage()]);

            return $this->workflowResponse(false, $exception->getMessage(), [], 422);
        } catch (RuntimeException $exception) {
            return $this->workflowResponse(false, $exception->getMessage(), [], 409);
        }
    }

    /** Retourne les cycles bloqués sous le contrat de défilement infini Tabulator. */
    public function blockedValidations(): void
    {
        $this->request->allowMethod(['get']);
        $this->Authorization->authorize($this->Applicationforms->newEmptyEntity(), 'viewBlockedValidations');
        /** @var \App\Model\Entity\User $viewer */
        $viewer = $this->request->getAttribute('identity')->getOriginalData();
        $cycles = (new ApplicationformValidationWorkflow())->findBlockedCycles($viewer);
        $size = max(1, (int)($this->request->getQuery('size') ?? 20));
        $page = max(1, (int)($this->request->getQuery('page') ?? 1));
        $rows = array_map(static function (BlockedValidationCycle $cycle): array {
            return [
                'id' => $cycle->applicationformNumber,
                'number' => $cycle->applicationformNumber,
                'begin_at' => $cycle->beginAt?->format('Y-m-d'),
                'department' => $cycle->department->name,
                'blocked_steps' => array_map(static fn(BlockedValidationStep $step): array => [
                    'sequence' => $step->step->sequence_number,
                    'role' => $step->role->name,
                    'activated_at' => $step->activatedAt->format('Y-m-d'),
                ], $cycle->blockedSteps),
                'activated_at' => $cycle->activatedAt->format('Y-m-d'),
                'blocked_since' => $cycle->blockedSince->format('Y-m-d'),
                'business_days' => $cycle->businessDays,
                'override_url' => $cycle->url,
                'grid_rights' => [
                    'actions' => [
                        'overrideValidation' => true,
                        'resendValidation' => true,
                    ],
                ],
            ];
        }, array_slice($cycles, ($page - 1) * $size, $size));
        $this->set([
            'data' => $rows,
            'last_page' => max(1, (int)ceil(count($cycles) / $size)),
        ]);
        $this->viewBuilder()->setOption('serialize', ['data', 'last_page']);
    }

    /**
     * Enregistre le vote de validation de l'opérateur.
     *
     * @param string $id Identifiant de la demande.
     * @return \Cake\Http\Response Réponse JSON.
     */
    public function voteValidation(string $id): Response
    {
        $this->request->allowMethod(['post']);
        $applicationform = $this->Applicationforms->get($id);
        $this->Authorization->authorize($applicationform, 'voteValidation');
        /** @var \App\Model\Entity\User $actor */
        $actor = $this->request->getAttribute('identity')->getOriginalData();
        $approved = ($this->request->getData('decision') === 'accepter');
        if (!$approved && $this->request->getData('decision') !== 'refuser') {
            return $this->workflowResponse(false, __('Décision de validation invalide.'), [], 422);
        }
        $stepId = filter_var($this->request->getData('step_id'), FILTER_VALIDATE_INT);
        if ($stepId === false || $stepId < 1) {
            return $this->workflowResponse(false, __('Étape de validation invalide.'), [], 422);
        }
        $workflow = new ApplicationformValidationWorkflow();
        $isProxy = filter_var(
            $this->request->getData('override', false),
            FILTER_VALIDATE_BOOLEAN,
        );
        $comment = (string)$this->request->getData('comment');
        try {
            $result = $workflow->vote(
                $applicationform,
                $actor,
                $stepId,
                $approved,
                $comment,
                $isProxy,
            );
            foreach ($result['nextRecipients'] as $recipient) {
                (new ValidationWorkflowMailer())->safeSend('validationStep', [$recipient, $applicationform]);
            }
            if ($result['final']) {
                $this->sendFinalResult($applicationform, $result['state'], $result['comment']);
            }

            return $this->workflowResponse(true, __('Votre vote a été enregistré.'), [
                'state' => $result['state'],
                'final' => $result['final'],
            ]);
        } catch (RuntimeException $exception) {
            return $this->workflowResponse(false, $exception->getMessage(), [], 422);
        }
    }

    /**
     * Supprime, de manière transactionnelle, le cycle d'une demande avant un nouveau lancement.
     *
     * @param string $id Identifiant de la demande.
     * @return \Cake\Http\Response Réponse JSON du résultat.
     */
    public function resetValidation(string $id): Response
    {
        $this->request->allowMethod(['post']);
        $applicationform = $this->Applicationforms->get($id);
        $this->Authorization->authorize($applicationform, 'resetValidation');

        try {
            $deleted = (new ApplicationformValidationWorkflow())->reset($applicationform);

            return $this->workflowResponse(
                true,
                __('Le cycle a été supprimé ; la demande peut de nouveau être lancée.'),
                ['deleted' => $deleted],
            );
        } catch (RuntimeException $exception) {
            return $this->workflowResponse(false, $exception->getMessage(), [], 409);
        }
    }

    /**
     * Relance par courriel les personnes concernées par les étapes bloquées.
     *
     * @param string $id Identifiant de la demande.
     * @return \Cake\Http\Response Réponse JSON.
     */
    public function remindValidation(string $id): Response
    {
        $this->request->allowMethod(['post']);
        $applicationform = $this->Applicationforms->get($id);
        $this->Authorization->authorize($applicationform, 'remindValidation');
        /** @var \App\Model\Entity\User $viewer */
        $viewer = $this->request->getAttribute('identity')->getOriginalData();
        $workflow = new ApplicationformValidationWorkflow();
        $cycle = null;
        foreach ($workflow->findBlockedCycles($viewer) as $candidate) {
            if ((int)$candidate->applicationform->id === (int)$applicationform->id) {
                $cycle = $candidate;
                break;
            }
        }
        if ($cycle === null) {
            return $this->workflowResponse(false, __('Le cycle de validation n’est plus bloqué.'), [], 409);
        }

        $recipients = $workflow->recipientsForBlockedCycle($cycle);
        if ($recipients === []) {
            return $this->workflowResponse(
                false,
                __('Aucun validateur concerné ne possède une adresse courriel.'),
                [],
                409,
            );
        }
        foreach ($recipients as $recipient) {
            (new ValidationWorkflowMailer())->safeSend('validationReminder', [$recipient, $applicationform]);
        }

        return $this->workflowResponse(
            true,
            __('La relance a été envoyée à {0} personne(s).', count($recipients)),
            ['recipients' => count($recipients)],
        );
    }

    /**
     * Retourne l'état du cycle de validation d'une demande.
     *
     * @param string $id Identifiant de la demande.
     */
    public function validationState(string $id): void
    {
        $this->request->allowMethod(['get']);
        $applicationform = $this->Applicationforms->get($id);
        $this->Authorization->authorize($applicationform, 'view');
        /** @var \App\Model\Entity\ValidationWorkflowRun|null $run */
        $run = $this->fetchTable('ValidationWorkflowRuns')->find()
            ->where([ValidationWorkflowRun::FIELD_APPLICATIONFORM_ID => $id])
            ->first();
        /** @var \App\Model\Entity\User $actor */
        $actor = $this->request->getAttribute('identity')->getOriginalData();
        $workflow = new ApplicationformValidationWorkflow();
        $blockedStepDetails = [];
        foreach ($workflow->findBlockedSteps($applicationform, $actor) as $blockedStep) {
            $blockedStepDetails[(int)$blockedStep->step->id] = $blockedStep;
        }
        $commentRequirements = $workflow->commentRequirements();
        $commentTemplates = ['accepter' => [], 'refuser' => []];
        $templates = $this->fetchTable('ValidationCommentTemplates')->find()
            ->select(['decision', 'label', 'content', 'position'])
            ->where(['active' => true])
            ->orderByAsc('decision')
            ->orderByAsc('position')
            ->all();
        foreach ($templates as $template) {
            $decision = (string)$template->get(ValidationCommentTemplate::FIELD_DECISION);
            $commentTemplates[$decision][] = [
                'label' => (string)$template->get(ValidationCommentTemplate::FIELD_LABEL),
                'content' => (string)$template->get(ValidationCommentTemplate::FIELD_CONTENT),
            ];
        }
        /** @var list<\App\Model\Entity\Applicationvalidationstep> $rawSteps */
        $rawSteps = $run === null ? [] : $this->fetchTable('Applicationvalidationsteps')->find()
            ->contain(['Roles', 'Validations'])
            ->where([
                Applicationvalidationstep::FIELD_VALIDATION_WORKFLOW_RUN_ID => $run->get(
                    ValidationWorkflowRun::FIELD_ID,
                ),
            ])
            ->orderByAsc('sequence_number')
            ->all()
            ->toList();
        $isSuperUser = $actor->isSuperUser();
        $isSimpleAdministrator = !$isSuperUser && $actor->hasRole(User::ROLE_ADMIN);
        $visibleSteps = $isSuperUser
            ? $rawSteps
            : array_values(array_filter(
                $rawSteps,
                function ($step) use ($workflow, $actor, $isSimpleAdministrator): bool {
                    if (
                        $isSimpleAdministrator
                        && $step->get(Applicationvalidationstep::FIELD_STATE) === 'a_venir'
                    ) {
                        return false;
                    }
                    if (
                        (int)$step->get(Applicationvalidationstep::FIELD_ROLE_ID)
                        === (int)$actor->get(User::FIELD_ROLE_ID)
                    ) {
                        return true;
                    }

                    return $workflow->canOverrideStep($step, $actor);
                },
            ));
        $steps = array_map(function ($step) use ($workflow, $applicationform, $actor, $blockedStepDetails): array {
            $canVoteNormally = $workflow->canVoteStep($step, $applicationform, $actor, false);
            $canOverride = $workflow->canOverrideStep($step, $actor);
            $blockedStep = $blockedStepDetails[(int)$step->get(Applicationvalidationstep::FIELD_ID)] ?? null;

            return [
                'id' => (int)$step->get(Applicationvalidationstep::FIELD_ID),
                'sequence_number' => (int)$step->get(Applicationvalidationstep::FIELD_SEQUENCE_NUMBER),
                'state' => (string)$step->get(Applicationvalidationstep::FIELD_STATE),
                'due_at' => $step->get(Applicationvalidationstep::FIELD_STATE) === 'en_attente'
                    ? $step->get(Applicationvalidationstep::FIELD_DUE_AT)
                    : null,
                'completed_at' => $step->get(Applicationvalidationstep::FIELD_COMPLETED_AT),
                'validated_at' => $step->validation?->validated,
                'comment' => $step->validation?->obs,
                'is_blocked' => $blockedStep !== null,
                'blocked_since' => $blockedStep?->blockedSince,
                'blocked_business_days' => $blockedStep?->businessDays,
                'role' => [
                    'id' => (int)$step->get(Applicationvalidationstep::FIELD_ROLE_ID),
                    'name' => (string)($step->role?->get(Role::FIELD_NAME) ?? __(
                        'Rôle n°{0}',
                        $step->get(Applicationvalidationstep::FIELD_ROLE_ID),
                    )),
                ],
                'can_vote' => $canVoteNormally || $canOverride,
                'can_override' => $canOverride,
                'is_proxy_vote' => !$canVoteNormally && $canOverride,
            ];
        }, $visibleSteps);
        $progressSteps = $isSuperUser ? $rawSteps : $visibleSteps;
        $completedSteps = count(array_filter(
            $progressSteps,
            static fn($step): bool => in_array($step->state, ['acceptee', 'refusee'], true),
        ));
        $progress = [
            'completed' => $completedSteps,
            'total' => count($progressSteps),
            'percentage' => $progressSteps === [] ? 0 : (int)round(100 * $completedSteps / count($progressSteps)),
        ];
        $this->set(compact('run', 'steps', 'progress', 'commentRequirements', 'commentTemplates'));
        $this->viewBuilder()->setOption('serialize', [
            'run', 'steps', 'progress', 'commentRequirements', 'commentTemplates',
        ]);
    }

    /**
     * Construit une réponse JSON homogène pour le workflow.
     *
     * @param bool $success Indique si l'opération a réussi.
     * @param string $message Message destiné à l'utilisateur.
     * @param array<mixed> $details Détails complémentaires ou erreurs de validation.
     * @param int $status Code HTTP.
     * @return \Cake\Http\Response Réponse JSON.
     */
    private function workflowResponse(bool $success, string $message, array $details, int $status = 200): Response
    {
        return $this->response->withType('application/json')->withStatus($status)
            ->withStringBody((string)json_encode([
                'success' => $success,
                'message' => $message,
                'details' => $success ? $details : [],
                'errors' => $success ? [] : ($details === [] ? [$message] : $details),
            ]));
    }

    /**
     * Envoie le résultat final du cycle aux destinataires concernés.
     *
     * @param \App\Model\Entity\Applicationform $applicationform Demande concernée.
     * @param string $state État final du cycle.
     * @param string|null $comment Commentaire associé au vote final.
     */
    private function sendFinalResult(Applicationform $applicationform, string $state, ?string $comment): void
    {
        $loaded = $this->Applicationforms->get(
            $applicationform->get(Applicationform::FIELD_ID),
            contain: ['Users', 'Managers', 'Departments' => ['Managers']],
        );
        $recipients = [$loaded->user];
        if ($loaded->manager !== null) {
            $recipients[] = $loaded->manager;
        }
        foreach ($loaded->department->get('managers') ?? [] as $manager) {
            $recipients[] = $manager;
        }
        /** @var \App\Model\Entity\Applicationvalidationstep|null $lastStep */
        $lastStep = $this->fetchTable('Applicationvalidationsteps')->find()
            ->where(['applicationform_id' => $loaded->id])
            ->orderByDesc('sequence_number')->first();
        if ($lastStep !== null) {
            $sgrUsers = $this->fetchTable('Users')->find()
                ->innerJoinWith('UserDepartments', function (SelectQuery $query) use ($loaded): SelectQuery {
                    return $query->where(['UserDepartments.department_id' => $loaded->department_id]);
                })
                ->where([
                    'Users.role_id' => $lastStep->get(Applicationvalidationstep::FIELD_ROLE_ID),
                    'Users.deleted IS' => null,
                ])->all();
            /** @var iterable<\App\Model\Entity\User> $sgrUsers */
            $sgrUsers = $sgrUsers;
            foreach ($sgrUsers as $sgrUser) {
                $recipients[] = $sgrUser;
            }
        }
        $sent = [];
        foreach ($recipients as $recipient) {
            $email = $recipient?->get(User::FIELD_EMAIL);
            if ($recipient !== null && is_string($email) && !isset($sent[$email])) {
                $sent[$email] = true;
                (new ValidationWorkflowMailer())->safeSend('finalResult', [$recipient, $loaded, $state, $comment]);
            }
        }
    }

    /**
     * Informe les administrateurs associés au département lorsqu'une précondition bloque le cycle.
     *
     * @param list<string> $issues Préconditions de lancement non satisfaites.
     */
    private function sendBlockedStartAlert(Applicationform $applicationform, array $issues): void
    {
        $administrators = $this->fetchTable('Users')->find()
            ->innerJoinWith('UserDepartments', function (SelectQuery $query) use ($applicationform): SelectQuery {
                return $query->where(['UserDepartments.department_id' => $applicationform->department_id]);
            })
            ->where([
                'Users.role_id' => User::ROLE_ADMIN,
                'Users.deleted IS' => null,
            ])
            ->distinct(['Users.id'])
            ->all();

        foreach ($administrators as $administrator) {
            (new ValidationWorkflowMailer())->safeSend(
                'validationBlocked',
                [$administrator, $applicationform, $issues],
            );
        }
    }

    /** @inheritDoc */
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setClassName('Json');
    }

    /** @inheritDoc */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->Authorization->skipAuthorization();
    }

    /**
     * Endpoint : GET /api/applicationforms/get-form-schema.json
     * Distribue les permissions sur les champs et les listes de référence filtrées par périmètre (visibleTo).
     */
    public function getFormSchema(): void
    {
        $this->request->allowMethod(['get']);

        $service = new FieldAuthorizationService();
        $identity = $this->request->getAttribute('identity');

        /** @var \App\Model\Entity\User $currentUser */
        $currentUser = $identity->getOriginalData();

        // Récupération du schéma ACL sur la ressource Applicationforms (ADR 0042)
        $schema = $service->getFieldSchema($identity, 'Applicationforms');

        // Instanciation des tables
        /** @var \App\Model\Table\DepartmentsTable $departmentsTable **/
        $departmentsTable = TableRegistry::getTableLocator()->get('Departments');
        $contracttypesTable = TableRegistry::getTableLocator()->get('Contracttypes');
        $hiringreasonsTable = TableRegistry::getTableLocator()->get('Hiringreasons');
        $profCategoriesTable = TableRegistry::getTableLocator()->get('Professionalcategories');
        $worktimesTable = TableRegistry::getTableLocator()->get('Worktimes');
        $periodsTable = TableRegistry::getTableLocator()->get('Periods');
        $budgetfeaturesTable = TableRegistry::getTableLocator()->get('Budgetfeatures');
        $yesnosTable = TableRegistry::getTableLocator()->get('Yesnos');
        $usersTable = TableRegistry::getTableLocator()->get('Users');

        // Application systématique du finder 'visibleTo' avec l'utilisateur courant (ADR 0041)
        $departments = $departmentsTable->findTreeSelectFormat($currentUser);

        $contracttypes = $contracttypesTable
            ->find('visibleTo', user: $currentUser)
            ->find('list', keyField: 'id', valueField: 'name')
            ->toArray();

        $hiringreasons = $hiringreasonsTable
            ->find('visibleTo', user: $currentUser)
            ->find('list', keyField: 'id', valueField: 'name')
            ->toArray();

        $professionalcategories = $profCategoriesTable
            ->find('visibleTo', user: $currentUser)
            ->find('list', keyField: 'id', valueField: 'name')
            ->toArray();

        $worktimes = $worktimesTable
            ->find('visibleTo', user: $currentUser)
            ->find('list', keyField: 'id', valueField: 'name')
            ->toArray();

        $periods = $periodsTable
            ->find('visibleTo', user: $currentUser)
            ->find('list', keyField: Period::FIELD_ID, valueField: Period::FIELD_NAME)
            ->toArray();

        $budgetfeatures = $budgetfeaturesTable
            ->find('visibleTo', user: $currentUser)
            ->find('list', keyField: 'id', valueField: 'name')
            ->toArray();

        $yesnos = $yesnosTable
            ->find('visibleTo', user: $currentUser)
            ->find('list', keyField: 'id', valueField: 'name')
            ->toArray();

        $collaborators = $usersTable
            ->find('visibleTo', user: $currentUser)
            ->find('list', keyField: 'id', valueField: 'email')
            ->toArray();

        $this->set(compact(
            'schema',
            'departments',
            'contracttypes',
            'hiringreasons',
            'professionalcategories',
            'worktimes',
            'periods',
            'budgetfeatures',
            'yesnos',
            'collaborators',
        ));

        $this->viewBuilder()->setOption('serialize', [
            'schema',
            'departments',
            'contracttypes',
            'hiringreasons',
            'professionalcategories',
            'worktimes',
            'periods',
            'budgetfeatures',
            'yesnos',
            'collaborators',
        ]);
    }

    /** Calcule le salaire de base sans persister la demande. */
    public function calculateSalary(): Response
    {
        $this->request->allowMethod(['post']);
        $this->Authorization->authorize($this->Applicationforms->newEmptyEntity(), 'add');

        try {
            $periodCode = (string)$this->request->getData('period_code');
            if ($periodCode === '' && $this->request->getData('period_id') !== null) {
                $period = $this->fetchTable('Periods')->get((int)$this->request->getData('period_id'));
                $periodCode = (string)$period->get(Period::FIELD_CODE);
            }
            $salaryBase = (new SalaryCalculationService())->calculateForPeriodCode(
                $periodCode,
                (string)$this->request->getData('grossremuneration'),
                $this->request->getData('working_days') === null
                    ? null
                    : (int)$this->request->getData('working_days'),
                $this->configuredKCoefficient(),
            );
        } catch (InvalidArgumentException $exception) {
            return $this->workflowResponse(false, $exception->getMessage(), [], 422);
        }

        return $this->response->withType('application/json')->withStringBody((string)json_encode([
            'success' => true,
            'message' => __('Salaire de base calculé.'),
            'salary_base' => $salaryBase,
        ]));
    }

    /** Retourne le coefficient k global, avec le défaut historique. */
    private function configuredKCoefficient(): float
    {
        $setting = $this->fetchTable('WorkflowSettings')->find()
            ->where(['name' => 'salary.k_coefficient'])->first();
        $value = $setting?->get('value');

        return is_numeric($value) && (float)$value > 0
            ? (float)$value
            : SalaryCalculationService::DEFAULT_K_COEFFICIENT;
    }

    /**
     * Endpoint : POST /api/applicationforms/add.json
     */
    public function add(): ?Response
    {
        $this->request->allowMethod(['post']);
        $this->Authorization->authorize($this->Applicationforms->newEmptyEntity(), 'add');

        $applicationform = $this->Applicationforms->newEmptyEntity();

        // Double protection : filtrage des champs contre la matrice ACL (ADR 0042)
        $identity = $this->request->getAttribute('identity');
        $filteredData = $this->authorizedRequestData('Applicationforms');

        // Assignation automatique de l'utilisateur créateur
        /** @var \App\Model\Entity\User $user */
        $user = $identity->getOriginalData();
        $filteredData[Applicationform::FIELD_USER_ID] = $user->get(User::FIELD_ID);

        $applicationform = $this->Applicationforms->patchEntity($applicationform, $filteredData);

        if ($this->Applicationforms->save($applicationform)) {
            return $this->response->withType('application/json')
                ->withStringBody((string)json_encode(['success' => true, 'id' => $applicationform->id]));
        }

        return $this->validationErrorResponse($applicationform, 'Applicationforms');
    }

    /**
     * Duplique une DAE visible en brouillon pour l'utilisateur courant.
     *
     * @param string $id Identifiant de la DAE source.
     * @return \Cake\Http\Response Réponse JSON.
     */
    public function duplicate(string $id): Response
    {
        $this->request->allowMethod(['post']);
        $source = $this->Applicationforms->get($id);
        $this->Authorization->authorize($source, 'duplicate');

        /** @var \App\Model\Entity\User $actor */
        $actor = $this->request->getAttribute('identity')->getOriginalData();
        $duplicate = $this->Applicationforms->duplicateFor($source, $actor);
        if ($duplicate === false) {
            return $this->workflowResponse(
                false,
                __('La demande ne peut pas être dupliquée. Vérifiez les champs obligatoires.'),
                [],
                422,
            );
        }

        return $this->workflowResponse(
            true,
            __('La demande a été dupliquée en brouillon.'),
            ['id' => $duplicate->get(Applicationform::FIELD_ID)],
        );
    }

    /** Archive logiquement une pige ou une facture sans supprimer son historique. */
    public function archive(string $id): Response
    {
        $this->request->allowMethod(['post']);
        $applicationform = $this->Applicationforms->get($id);
        $this->Authorization->authorize($applicationform, 'archive');
        if ($applicationform->get(Applicationform::FIELD_ARCHIVED) !== null) {
            return $this->workflowResponse(true, __('La demande est déjà archivée.'), []);
        }

        $applicationform->set(Applicationform::FIELD_ARCHIVED, DateTime::now());
        if (!$this->Applicationforms->save($applicationform, ['checkRules' => false])) {
            return $this->workflowResponse(false, __('La demande ne peut pas être archivée.'), [], 422);
        }

        return $this->workflowResponse(true, __('La demande a été archivée.'), []);
    }

    /**
     * Endpoint : PUT/PATCH /api/applicationforms/edit/{id}.json
     */
    public function edit(string $id): ?Response
    {
        $this->request->allowMethod(['post', 'put', 'patch']);

        $applicationform = $this->Applicationforms->get($id);
        $this->Authorization->authorize($applicationform, 'edit');

        $identity = $this->request->getAttribute('identity');
        $filteredData = $this->authorizedRequestData('Applicationforms');

        $activeRun = $this->fetchTable('ValidationWorkflowRuns')->find()
            ->where([
                ValidationWorkflowRun::FIELD_APPLICATIONFORM_ID => $applicationform->get(Applicationform::FIELD_ID),
                'state' => 'en_attente',
            ])->first();
        if (
            $activeRun !== null
            && isset($filteredData['department_id'])
            && (int)$filteredData[Applicationform::FIELD_DEPARTMENT_ID] !== (int)$applicationform->get(
                Applicationform::FIELD_DEPARTMENT_ID,
            )
        ) {
            return $this->workflowResponse(
                false,
                __('Le département ne peut pas être modifié pendant un cycle de validation.'),
                [],
                422,
            );
        }

        $applicationform = $this->Applicationforms->patchEntity($applicationform, $filteredData);
        $hasChanges = $applicationform->isDirty();

        if ($this->Applicationforms->save($applicationform)) {
            if ($activeRun !== null && $hasChanges) {
                /** @var \App\Model\Entity\User $operator */
                $operator = $identity->getOriginalData();
                $this->fetchTable('Comments')->saveOrFail($this->fetchTable('Comments')->newEntity([
                    'model' => 'Applicationforms',
                    'foreign_key' => $applicationform->get(Applicationform::FIELD_ID),
                    'type' => 'WORKFLOW_EDIT_AUDIT',
                    'content' => __(
                        'Modification pendant le cycle par {0} le {1}.',
                        $operator->display_name,
                        DateTime::now()->i18nFormat('dd/MM/yyyy HH:mm'),
                    ),
                    'user_id' => $operator->get(User::FIELD_ID),
                ]));
            }

            return $this->response->withType('application/json')
                ->withStringBody((string)json_encode(['success' => true]));
        }

        return $this->validationErrorResponse($applicationform, 'Applicationforms');
    }

    /**
     * Supprime une demande et son cycle éventuel pour un Super Administrateur.
     *
     * @param string $id Identifiant de la demande.
     * @return \Cake\Http\Response Réponse JSON.
     */
    public function delete(string $id): Response
    {
        $this->request->allowMethod(['delete']);
        $applicationform = $this->Applicationforms->get($id);
        $this->Authorization->authorize($applicationform, 'delete');

        try {
            $deleted = (new ApplicationformValidationWorkflow())->deleteApplicationform($applicationform);

            return $this->workflowResponse(
                true,
                __('La demande et son éventuel cycle de validation ont été supprimés.'),
                $deleted,
            );
        } catch (RuntimeException $exception) {
            return $this->workflowResponse(false, $exception->getMessage(), [], 409);
        }
    }

    /**
     * Méthode Index (GET /api/applicationforms.json)
     *
     * @return \Cake\Http\Response|null
     */
    public function index(): ?Response
    {
        $this->request->allowMethod(['get']);

        // Verrou de sécurité
        $this->Authorization->authorize($this->Applicationforms->newEmptyEntity(), 'index');

        $adapter = new TabulatorAdapter();
        $queryParams = $this->request->getQueryParams();

        /** @var \App\Model\Entity\User $currentUser */
        $currentUser = $this->request->getAttribute('identity')->getOriginalData();

        // 1. Préparation de la requête ORM AVEC le filtre de sécurité "visibleTo"
        $query = $this->Applicationforms->find('visibleTo', user: $currentUser)
            ->contain([
                'Departments',
                'Users',
                'Contracttypes',
                'Hiringreasons',
                'Budgetfeatures',
                'Periods',
                'Applicationformstatuses',
                'ValidationWorkflowRuns',
                'Comments',
                'Comments' => ['Users'], // Charge le fil de discussion et ses auteurs
            ])
            ->leftJoinWith('Applicationformstatuses');
        $query
            ->enableAutoFields(true)
            ->select([
                'validation_status' => 'Applicationformstatuses.validationstatus_id',
                'validation_sort' => $query->expr(
                    'CASE '
                    . 'WHEN Applicationformstatuses.rejected = 1 THEN 4 '
                    . 'WHEN Applicationformstatuses.accepted = 1 THEN 3 '
                    . 'WHEN Applicationformstatuses.en_cours = 1 THEN 2 '
                    . 'ELSE 1 END * 1000 '
                    . '+ COALESCE(Applicationformstatuses.valid_percentage, 0)',
                ),
            ]);
        if (!$this->request->getQuery('include_archived')) {
            $query->where(['Applicationforms.archived IS' => null]);
        }
        $this->applyApplicationformSearchFilters($query);

        // 2. Application des tris et filtres Tabulator
        try {
            $query = $adapter->adaptRequest($this->request, $query, [
                'validation_status' => 'Applicationformstatuses.validationstatus_id',
            ], [
                'validation_status' => 'validation_sort',
            ], [
                'validation_status' => static function (mixed $value, string $type): array {
                    if ((string)$value === '2') {
                        return ['Applicationformstatuses.en_cours', 1];
                    }

                    return ['Applicationformstatuses.validationstatus_id', $value];
                },
            ]);
        } catch (UnprocessableContentException $exception) {
            return $this->workflowResponse(false, $exception->getMessage(), [], 422);
        }

        // 3. Pagination native
        try {
            $paginatedData = $this->paginate($query, [
                'limit' => (int)($queryParams['size'] ?? 40), // Valeur conseillée pour le scroll progressif (ADR 0039)
                'page' => (int)($queryParams['page'] ?? 1),
            ]);
        } catch (NotFoundException $e) {
            // Si la page demandée dépasse le total, on force le retour à la page 1
            $this->request = $this->request->withQueryParams(array_merge($queryParams, ['page' => 1]));
            $paginatedData = $this->paginate($query, [
                'limit' => (int)($queryParams['size'] ?? 40),
                'page' => 1,
            ]);
        }

        foreach ($paginatedData->items() as $applicationform) {
            $this->hydrateMissingSalaryBase($applicationform);
        }

        // 4. Droits dynamiques de la grille
        $rightsFormatter = $this->createGridRightsFormatter([
            'launchValidation',
            'resetValidation',
            'viewpdf',
            'duplicate',
        ]);

        // 5. Rendu structuré pour Tabulator
        $output = $adapter->adaptResponse($paginatedData, $rightsFormatter);

        $this->set($output);
        $this->viewBuilder()->setOption('serialize', array_keys($output));

        return null;
    }

    /**
     * Calcule à la volée le salaire absent des anciennes demandes pour la grille.
     * La valeur n'est pas persistée ; les nouvelles sauvegardes restent calculées
     * par le callback ORM de la table Applicationforms.
     *
     * @param \App\Model\Entity\Applicationform $applicationform Demande affichée.
     * @return void
     */
    private function hydrateMissingSalaryBase(Applicationform $applicationform): void
    {
        $salaryBase = $applicationform->get(Applicationform::FIELD_SALARY_BASE);
        if ($salaryBase !== null && $salaryBase !== '') {
            return;
        }

        $period = $applicationform->get(Applicationform::FIELD_PERIOD);
        $periodCode = strtoupper(trim((string)$period?->get(Period::FIELD_CODE)));
        if ($periodCode === '' || $applicationform->get(Applicationform::FIELD_GROSSREMUNERATION) === null) {
            return;
        }

        try {
            $workingDays = $applicationform->get(Applicationform::FIELD_WORKING_DAYS);
            if ($workingDays === null && $periodCode === Period::CODE_EUR_PER_DAY) {
                $workingDays = $this->estimateWorkingDaysForGrid($applicationform);
            }

            $applicationform->set(
                Applicationform::FIELD_SALARY_BASE,
                (new SalaryCalculationService())->calculateForPeriodCode(
                    $periodCode,
                    $applicationform->get(Applicationform::FIELD_GROSSREMUNERATION),
                    $workingDays,
                    $this->configuredKCoefficient(),
                ),
            );
        } catch (InvalidArgumentException) {
            // Une demande incomplète reste affichable sans valeur calculée.
        }
    }

    /**
     * Estime les jours ouvrés inclusifs pour une ancienne demande journalière.
     *
     * @param \App\Model\Entity\Applicationform $applicationform Demande affichée.
     * @return int|null Nombre de jours ouvrés, ou null si les dates sont invalides.
     */
    private function estimateWorkingDaysForGrid(Applicationform $applicationform): ?int
    {
        $begin = $this->gridDate($applicationform->get(Applicationform::FIELD_BEGIN_AT));
        $end = $this->gridDate($applicationform->get(Applicationform::FIELD_END_AT));
        if ($begin === null || $end === null || $end < $begin) {
            return null;
        }

        return (new WorkingDaysService())->countWorkingDays($begin, $end);
    }

    /** Convertit une date ORM ou textuelle en date civile exploitable. */
    private function gridDate(mixed $value): ?DateTimeImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value)->setTime(0, 0);
        }
        if (is_object($value) && method_exists($value, 'format')) {
            $value = $value->format('Y-m-d');
        }
        if (!is_string($value) || $value === '') {
            return null;
        }

        $dateText = substr($value, 0, 10);
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $dateText);

        return $date !== false && $date->format('Y-m-d') === $dateText ? $date : null;
    }

    /**
     * Applique les filtres métier complémentaires à la grille distante.
     *
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\Applicationform> $query Requête distante.
     */
    private function applyApplicationformSearchFilters(SelectQuery $query): void
    {
        $search = trim((string)$this->request->getQuery('search', ''));
        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(['OR' => [
                'Applicationforms.applicantname LIKE' => $like,
                'Applicationforms.applicant_first_name LIKE' => $like,
                'Applicationforms.applicant_last_name LIKE' => $like,
            ]]);
        }
        $cgr = trim((string)$this->request->getQuery('cgr', ''));
        if ($cgr !== '') {
            $query->where(['Applicationforms.cgr LIKE' => '%' . $cgr . '%']);
        }
        $contractTypeId = $this->request->getQuery('contracttype_id');
        if (filter_var($contractTypeId, FILTER_VALIDATE_INT) !== false) {
            $query->where(['Applicationforms.contracttype_id' => (int)$contractTypeId]);
        }
        $dateFilters = [
            'begin_from' => 'begin_at >=',
            'begin_to' => 'begin_at <=',
            'end_from' => 'end_at >=',
            'end_to' => 'end_at <=',
        ];
        foreach ($dateFilters as $parameter => $condition) {
            $value = trim((string)$this->request->getQuery($parameter, ''));
            if ($value !== '') {
                $query->where(['Applicationforms.' . $condition => $value]);
            }
        }
        $workflowStatus = trim((string)$this->request->getQuery('workflow_status', ''));
        if ($workflowStatus !== '') {
            $query->where(['ValidationWorkflowRuns.state' => $workflowStatus]);
        }
    }

    /**
     * Endpoint : GET /api/applicationforms/getCgrConfig/{departmentId}.json
     */
    public function getCgrConfig(string $departmentId): void
    {
        $this->request->allowMethod(['get']);
        $this->Authorization->skipAuthorization();

        $resolver = new CgrResolverService();
        $config = $resolver->getCgrConfigForDepartment((int)$departmentId);

        $this->set($config);
        $this->viewBuilder()->setOption('serialize', array_keys($config));
    }
}
