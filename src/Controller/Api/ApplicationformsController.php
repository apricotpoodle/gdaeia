<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use App\Mailer\ValidationWorkflowMailer;
use App\Model\Entity\Applicationform;
use App\Model\Entity\Applicationvalidationstep;
use App\Model\Entity\Role;
use App\Model\Entity\User;
use App\Model\Entity\ValidationCommentTemplate;
use App\Model\Entity\ValidationWorkflowRun;
use App\Service\CgrResolverService;
use App\Service\DataGrid\TabulatorAdapter;
use App\Service\Security\FieldAuthorizationService;
use App\Service\Workflow\ApplicationformValidationWorkflow;
use App\Service\Workflow\BlockedValidationCycle;
use App\Service\Workflow\BlockedValidationStep;
use App\Service\Workflow\WorkflowStartFailureException;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Exception\UnprocessableContentException;
use Cake\Http\Response;
use Cake\I18n\DateTime;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\TableRegistry;
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
                'due_at' => $step->get(Applicationvalidationstep::FIELD_DUE_AT),
                'completed_at' => $step->get(Applicationvalidationstep::FIELD_COMPLETED_AT),
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
            contain: ['Users', 'Departments' => ['Managers']],
        );
        $recipients = [$loaded->user];
        if ($loaded->department->manager !== null) {
            $recipients[] = $loaded->department->manager;
        }
        $sent = [];
        foreach ($recipients as $recipient) {
            if ($recipient !== null && !isset($sent[$recipient->email])) {
                $sent[$recipient->email] = true;
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
            ->find('list', keyField: 'id', valueField: 'name')
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

    /**
     * Endpoint : POST /api/applicationforms/add.json
     */
    public function add(): ?Response
    {
        $this->request->allowMethod(['post']);
        $this->Authorization->authorize($this->Applicationforms->newEmptyEntity(), 'add');

        $applicationform = $this->Applicationforms->newEmptyEntity();

        $authService = new FieldAuthorizationService();
        $identity = $this->request->getAttribute('identity');

        // Double protection : Filtrage des champs contre la matrice ACL (ADR 0042)
        $schema = $authService->getFieldSchema($identity, 'Applicationforms');
        $filteredData = $authService->filterRequestData($this->request->getData(), $schema);

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

    /**
     * Endpoint : PUT/PATCH /api/applicationforms/edit/{id}.json
     */
    public function edit(string $id): ?Response
    {
        $this->request->allowMethod(['post', 'put', 'patch']);

        $applicationform = $this->Applicationforms->get($id);
        $this->Authorization->authorize($applicationform, 'edit');

        $authService = new FieldAuthorizationService();
        $identity = $this->request->getAttribute('identity');

        $schema = $authService->getFieldSchema($identity, 'Applicationforms');
        $filteredData = $authService->filterRequestData($this->request->getData(), $schema);

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
                'Applicationformstatuses',
                'ValidationWorkflowRuns',
                'Comments',
                'Comments' => ['Users'], // Charge le fil de discussion et ses auteurs
            ])
            ->leftJoinWith('Applicationformstatuses')
            ->enableAutoFields(true)
            ->select([
                'validation_status' => 'Applicationformstatuses.validationstatus_id',
            ]);

        // 2. Application des tris et filtres Tabulator
        try {
            $query = $adapter->adaptRequest($this->request, $query, [
                'validation_status' => 'Applicationformstatuses.validationstatus_id',
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
