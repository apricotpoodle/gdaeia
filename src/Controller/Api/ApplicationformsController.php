<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use App\Mailer\ValidationWorkflowMailer;
use App\Model\Entity\Applicationform;
use App\Model\Entity\User;
use App\Service\CgrResolverService;
use App\Service\DataGrid\TabulatorAdapter;
use App\Service\Security\FieldAuthorizationService;
use App\Service\Workflow\ApplicationformValidationWorkflow;
use App\Service\Workflow\WorkflowStartFailureException;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
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
        $isProxy = (int)$actor->role_id === 1;
        try {
            $result = $workflow->vote(
                $applicationform,
                $actor,
                $stepId,
                $approved,
                (string)$this->request->getData('comment'),
                $isProxy,
            );
            foreach ($result['nextRecipients'] as $recipient) {
                (new ValidationWorkflowMailer())->safeSend('validationStep', [$recipient, $applicationform]);
            }
            if ($result['final']) {
                $this->sendFinalResult($applicationform, $result['state'], (string)$this->request->getData('comment'));
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
            ->where(['applicationform_id' => $id])
            ->first();
        /** @var \App\Model\Entity\User $actor */
        $actor = $this->request->getAttribute('identity')->getOriginalData();
        $workflow = new ApplicationformValidationWorkflow();
        /** @var list<\App\Model\Entity\Applicationvalidationstep> $rawSteps */
        $rawSteps = $run === null ? [] : $this->fetchTable('Applicationvalidationsteps')->find()
            ->contain(['Roles'])
            ->where(['validation_workflow_run_id' => $run->id])
            ->orderByAsc('sequence_number')
            ->all()
            ->toList();
        $isProxy = (int)$actor->role_id === User::ROLE_ADMIN;
        $steps = array_map(function ($step) use ($workflow, $applicationform, $actor, $isProxy): array {
            $canVote = $step->state === 'en_attente'
                && $workflow->canVoteStep($step, $applicationform, $actor, $isProxy);

            return [
                'id' => (int)$step->id,
                'sequence_number' => (int)$step->sequence_number,
                'state' => (string)$step->state,
                'due_at' => $step->due_at,
                'completed_at' => $step->completed_at,
                'comment' => $step->comment,
                'role' => [
                    'id' => (int)$step->role_id,
                    'name' => (string)($step->role->name ?? __('Rôle n°{0}', $step->role_id)),
                ],
                'can_vote' => $canVote,
                'is_proxy_vote' => $canVote && $isProxy,
            ];
        }, $rawSteps);
        $completedSteps = count(array_filter(
            $rawSteps,
            static fn($step): bool => in_array($step->state, ['acceptee', 'refusee'], true),
        ));
        $progress = [
            'completed' => $completedSteps,
            'total' => count($rawSteps),
            'percentage' => $rawSteps === [] ? 0 : (int)round(100 * $completedSteps / count($rawSteps)),
        ];
        $this->set(compact('run', 'steps', 'progress'));
        $this->viewBuilder()->setOption('serialize', ['run', 'steps', 'progress']);
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
        $loaded = $this->Applicationforms->get($applicationform->id, contain: ['Users', 'Departments' => ['Managers']]);
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
        $filteredData['user_id'] = $user->id;

        $applicationform = $this->Applicationforms->patchEntity($applicationform, $filteredData);

        if ($this->Applicationforms->save($applicationform)) {
            return $this->response->withType('application/json')
                ->withStringBody((string)json_encode(['success' => true, 'id' => $applicationform->id]));
        }

        /** @var \Cake\Datasource\EntityInterface $applicationform */
        return $this->handleValidationError($applicationform);
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
                'applicationform_id' => $applicationform->id,
                'state' => 'en_attente',
            ])->first();
        if (
            $activeRun !== null
            && isset($filteredData['department_id'])
            && (int)$filteredData['department_id'] !== (int)$applicationform->department_id
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
                    'foreign_key' => $applicationform->id,
                    'type' => 'WORKFLOW_EDIT_AUDIT',
                    'content' => __(
                        'Modification pendant le cycle par {0} le {1}.',
                        $operator->display_name,
                        DateTime::now()->i18nFormat('dd/MM/yyyy HH:mm'),
                    ),
                    'user_id' => $operator->id,
                ]));
            }

            return $this->response->withType('application/json')
                ->withStringBody((string)json_encode(['success' => true]));
        }

        /** @var \Cake\Datasource\EntityInterface $applicationform */
        return $this->handleValidationError($applicationform);
    }

    /**
     * Gestion centralisée des erreurs de validation
     *
     * @param \Cake\Datasource\EntityInterface $entity
     * @return \Cake\Http\Response
     */
    private function handleValidationError(EntityInterface $entity): Response
    {
        $error = $this->findFirstValidationError($entity->getErrors());
        $message = $error === null
            ? __('Impossible d\'enregistrer la demande : le serveur n\'a pas retourné de détail de validation.')
            : __('Champ « {0} » : {1}', $this->getApplicationformFieldLabel($error[0]), $error[1]);

        return $this->response->withType('application/json')
            ->withStatus(400)
            ->withStringBody((string)json_encode(['success' => false, 'message' => $message]));
    }

    /**
     * Extrait la première erreur en préservant le champ métier qui la porte.
     *
     * @param array<string, mixed> $errors Erreurs produites par l'ORM CakePHP.
     * @param string|null $rootField Champ racine pour les associations imbriquées.
     * @return array{0: string, 1: string}|null
     */
    private function findFirstValidationError(array $errors, ?string $rootField = null): ?array
    {
        foreach ($errors as $field => $details) {
            $currentRootField = $rootField ?? (string)$field;
            if (is_string($details)) {
                return [$currentRootField, $details];
            }

            if (is_array($details)) {
                $error = $this->findFirstValidationError($details, $currentRootField);
                if ($error !== null) {
                    return $error;
                }
            }
        }

        return null;
    }

    /**
     * Retourne le libellé fonctionnel d'un champ de demande de recrutement.
     *
     * @param string $field Nom technique du champ.
     * @return string
     */
    private function getApplicationformFieldLabel(string $field): string
    {
        return [
            'department_id' => __('Département'),
            'user_id' => __('Créateur de la demande'),
            'cgr' => __('Code CGR'),
            'contracttype_id' => __('Type de contrat'),
            'hiringreason_id' => __('Motif de recrutement'),
            'reasonforreplacement' => __('Précision du motif'),
            'budgetfeature_id' => __('Imputation budgétaire'),
            'jobtitle' => __('Intitulé du poste'),
            'professionalcategory_id' => __('Catégorie professionnelle'),
            'worktime_id' => __('Temps de travail'),
            'workingtimedistribution' => __('Répartition du temps de travail'),
            'grossremuneration' => __('Rémunération brute'),
            'period_id' => __('Périodicité'),
            'qualification' => __('Qualification'),
            'begin_at' => __('Date de début'),
            'end_at' => __('Date de fin'),
            'applicantname' => __('Nom du candidat'),
            'yesno_id' => __('Champ Oui/Non'),
        ][$field] ?? $field;
    }

    /**
     * Méthode Index (GET /api/applicationforms.json)
     *
     * @return void
     */
    public function index(): void
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
            ]);

        // 2. Application des tris et filtres Tabulator
        $query = $adapter->adaptRequest($this->request, $query);

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
        $rightsFormatter = $this->createGridRightsFormatter(['launchValidation', 'resetValidation']);

        // 5. Rendu structuré pour Tabulator
        $output = $adapter->adaptResponse($paginatedData, $rightsFormatter);

        $this->set($output);
        $this->viewBuilder()->setOption('serialize', array_keys($output));
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
