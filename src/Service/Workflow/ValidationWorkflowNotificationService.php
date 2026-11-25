<?php
declare(strict_types=1);

namespace App\Service\Workflow;

use App\Mailer\ValidationWorkflowMailer;
use App\Model\Entity\Applicationform;
use App\Model\Entity\Applicationvalidationstep;
use App\Model\Entity\Department;
use App\Model\Entity\User;
use App\Model\Entity\ValidationWorkflowRun;
use App\Policy\ApplicationformPolicy;
use App\Service\NotificationDeliveryService;
use App\Service\Security\PermissionResolver;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

/** Résout les destinataires autorisés et déduplique les notifications GDAE. */
final class ValidationWorkflowNotificationService
{
    private Table $applicationforms;
    private Table $steps;
    private Table $users;
    private ApplicationformPolicy $policy;
    private ApplicationformValidationWorkflow $workflow;
    private NotificationDeliveryService $deliveries;

    /** Initialise les services d’autorisation et de suivi des livraisons. */
    public function __construct(?PermissionResolver $permissions = null)
    {
        $permissions ??= new PermissionResolver();
        $locator = TableRegistry::getTableLocator();
        $this->applicationforms = $locator->get('Applicationforms');
        $this->steps = $locator->get('Applicationvalidationsteps');
        $this->users = $locator->get('Users');
        $this->policy = new ApplicationformPolicy($permissions);
        $this->workflow = new ApplicationformValidationWorkflow($permissions);
        /** @var \App\Model\Table\NotificationDeliveriesTable $deliveries */
        $deliveries = $locator->get('NotificationDeliveries');
        $this->deliveries = new NotificationDeliveryService($deliveries);
    }

    /** Notifie les validateurs et Admins autorisés après la création du cycle. */
    public function notifyStart(Applicationform $applicationform, ValidationWorkflowRun $run): int
    {
        $runId = (int)$run->get(ValidationWorkflowRun::FIELD_ID);

        return $this->sendToRecipients(
            'validation_start',
            'run:' . $runId,
            $this->collectCurrentStepRecipients($applicationform, $runId),
            'validationStep',
            [$applicationform],
        );
    }

    /** Notifie une nouvelle étape ou le résultat final après le commit du vote. */
    public function notifyVote(
        Applicationform $applicationform,
        int $runId,
        int $votedStepId,
        string $state,
        bool $final,
        ?string $comment,
    ): int {
        $eventKey = sprintf('run:%d:step:%d:state:%s', $runId, $votedStepId, $state);
        if ($final) {
            $accepted = $state === ValidationWorkflowRun::STATE_ACCEPTED;
            $recipients = $accepted
                ? $this->collectFinalAcceptanceRecipients($applicationform)
                : $this->collectFinalRejectionRecipients($applicationform);

            return $this->sendToRecipients(
                'validation_final_result',
                $eventKey,
                $recipients,
                'finalResult',
                [$applicationform, $state, $comment],
            );
        }

        /** @var \App\Model\Entity\Applicationvalidationstep $votedStep */
        $votedStep = $this->steps->get($votedStepId);
        if (!$this->hasNewActiveStep($votedStep, $runId)) {
            return 0;
        }

        return $this->sendToRecipients(
            'validation_step',
            $eventKey,
            $this->collectCurrentStepRecipients($applicationform, $runId),
            'validationStep',
            [$applicationform],
        );
    }

    /** Retourne les personnes de l’étape active et les Admins capables de l’outrepasser. */

    /** @return list<\App\Model\Entity\User> */
    public function collectCurrentStepRecipients(Applicationform $applicationform, int $runId): array
    {
        /** @var iterable<\App\Model\Entity\Applicationvalidationstep> $steps */
        $steps = $this->steps->find()->where([
            Applicationvalidationstep::FIELD_APPLICATIONFORM_ID => $applicationform->get(
                Applicationform::FIELD_ID,
            ),
            Applicationvalidationstep::FIELD_VALIDATION_WORKFLOW_RUN_ID => $runId,
            Applicationvalidationstep::FIELD_STATE => 'en_attente',
        ])->all();
        $recipients = [];
        foreach ($steps as $step) {
            foreach ($this->workflow->eligibleUsers($applicationform, (int)$step->get('role_id')) as $user) {
                $recipients[] = $user;
            }
            foreach ($this->administrators() as $administrator) {
                if (
                    $this->policy->canView($administrator, $applicationform)
                    && $this->workflow->canOverrideStep($step, $administrator)
                ) {
                    $recipients[] = $administrator;
                }
            }
        }

        return $this->deduplicateRecipients($recipients);
    }

    /** Retourne l’auteure, les responsables associés et les Admins pouvant lire la DAE. */

    /** @return list<\App\Model\Entity\User> */
    public function collectFinalAcceptanceRecipients(Applicationform $applicationform): array
    {
        $loaded = $this->loadApplicationform($applicationform);
        $recipients = [];
        $author = $loaded->get('user');
        if ($author instanceof User) {
            $recipients[] = $author;
        }
        $department = $loaded->get('department');
        if ($department instanceof Department) {
            foreach ($department->get('managers') ?? [] as $manager) {
                if ($manager instanceof User) {
                    $recipients[] = $manager;
                }
            }
        }
        foreach ($this->visibleAdministrators($loaded) as $administrator) {
            $recipients[] = $administrator;
        }

        return $this->deduplicateRecipients($recipients);
    }

    /** Envoie le résultat d’un refus aux mêmes rôles déjà notifiés par l’API. */

    /** @return list<\App\Model\Entity\User> */
    private function collectFinalRejectionRecipients(Applicationform $applicationform): array
    {
        $loaded = $this->loadApplicationform($applicationform);
        $recipients = [];
        $author = $loaded->get('user');
        if ($author instanceof User) {
            $recipients[] = $author;
        }
        $department = $loaded->get('department');
        if ($department instanceof Department) {
            foreach ($department->get('managers') ?? [] as $manager) {
                if ($manager instanceof User) {
                    $recipients[] = $manager;
                }
            }
        }

        /** @var \App\Model\Entity\Applicationvalidationstep|null $lastStep */
        $lastStep = $this->steps->find()
            ->where([Applicationvalidationstep::FIELD_APPLICATIONFORM_ID => $loaded->get('id')])
            ->orderByDesc(Applicationvalidationstep::FIELD_SEQUENCE_NUMBER)
            ->first();
        if ($lastStep !== null) {
            foreach ($this->workflow->eligibleUsers($loaded, (int)$lastStep->get('role_id')) as $user) {
                $recipients[] = $user;
            }
        }

        return $this->deduplicateRecipients($recipients);
    }

    /** Recherche les comptes administrateurs et super-administrateurs actifs. */

    /** @return list<\App\Model\Entity\User> */
    private function administrators(): array
    {
        /** @var list<\App\Model\Entity\User> $users */
        $users = $this->users->find()->where([
            'Users.deleted IS' => null,
            'OR' => [
                ['Users.role_id' => User::ROLE_ADMIN],
                ['Users.issuperuser' => true],
            ],
        ])->all()->toList();

        return $users;
    }

    /** Retourne les administrateurs dont la Policy autorise l’accès à la demande. */

    /** @return list<\App\Model\Entity\User> */
    private function visibleAdministrators(Applicationform $applicationform): array
    {
        return array_values(array_filter(
            $this->administrators(),
            fn(User $user): bool => $this->policy->canView($user, $applicationform),
        ));
    }

    /** Charge les relations nécessaires à la résolution des destinataires finaux. */
    private function loadApplicationform(Applicationform $applicationform): Applicationform
    {
        /** @var \App\Model\Entity\Applicationform $loaded */
        $loaded = $this->applicationforms->get(
            $applicationform->get(Applicationform::FIELD_ID),
            contain: ['Users', 'Departments' => ['Managers']],
        );

        return $loaded;
    }

    /** Vérifie qu’un vote vient d’activer une étape suivante du même cycle. */
    private function hasNewActiveStep(Applicationvalidationstep $votedStep, int $runId): bool
    {
        return $this->steps->exists([
            Applicationvalidationstep::FIELD_VALIDATION_WORKFLOW_RUN_ID => $runId,
            Applicationvalidationstep::FIELD_STATE => 'en_attente',
            Applicationvalidationstep::FIELD_SEQUENCE_NUMBER . ' >' => $votedStep->get(
                Applicationvalidationstep::FIELD_SEQUENCE_NUMBER,
            ),
        ]);
    }

    /** Envoie aux destinataires uniques et compte les succès confirmés.
     *
     * @param list<\App\Model\Entity\User> $recipients
     * @param list<mixed> $arguments
     */
    private function sendToRecipients(
        string $type,
        string $eventKey,
        array $recipients,
        string $action,
        array $arguments,
    ): int {
        $sentCount = 0;
        foreach ($this->deduplicateRecipients($recipients) as $recipient) {
            $mailer = new ValidationWorkflowMailer();
            $sent = $this->deliveries->sendOnce(
                $type,
                $eventKey,
                $recipient,
                static fn(): bool => $mailer->safeSend($action, [$recipient, ...$arguments]),
            );
            if ($sent) {
                $sentCount++;
            }
        }

        return $sentCount;
    }

    /** Normalise les adresses et écarte les comptes supprimés ou incomplets.
     *
     * @param list<\App\Model\Entity\User> $recipients
     * @return list<\App\Model\Entity\User>
     */
    private function deduplicateRecipients(array $recipients): array
    {
        $unique = [];
        foreach ($recipients as $recipient) {
            if (!$recipient instanceof User || $recipient->get(User::FIELD_DELETED) !== null) {
                continue;
            }
            $email = $recipient->get(User::FIELD_EMAIL);
            if (!is_string($email) || trim($email) === '') {
                continue;
            }
            $unique[strtolower(trim($email))] ??= $recipient;
        }

        return array_values($unique);
    }
}
