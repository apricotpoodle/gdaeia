<?php
declare(strict_types=1);

namespace App\Test\TestCase\Integration\Service;

use App\Model\Entity\Applicationform;
use App\Service\Workflow\ValidationWorkflowNotificationService;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/** Vérifie les destinataires des notifications du workflow GDAE sur MySQL. */
class ValidationWorkflowNotificationServiceTest extends TestCase
{
    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Applicationforms',
        'app.Applicationvalidationsteps',
        'app.Departments',
        'app.DepartmentManagers',
        'app.NotificationDeliveries',
        'app.Permissions',
        'app.PermissionScopes',
        'app.Role1Permissions',
        'app.Roles',
        'app.UserDepartments',
        'app.Users',
        'app.Validationsequences',
        'app.ValidationWorkflowRuns',
    ];

    /** Les notifications incluent les validateurs et seuls les Admins autorisés. */
    public function testCollecteLesValidateursEtLesAdministrateursAutorises(): void
    {
        $runId = $this->prepareWorkflowData();
        $applicationform = $this->applicationform();
        $service = new ValidationWorkflowNotificationService();

        $recipients = $service->collectCurrentStepRecipients($applicationform, $runId);

        $this->assertSame([
            'admin-visible@example.test',
            'super-admin@example.test',
            'validateur@example.test',
        ], $this->emails($recipients));
    }

    /** Un Admin sans droit d’outrepasser ne reçoit pas l’alerte de l’étape. */
    public function testExclutUnAdministrateurSansDroitDOutrepasser(): void
    {
        $runId = $this->prepareWorkflowData();
        ConnectionManager::get('test')->delete('role_permissions', ['id' => 74]);
        $service = new ValidationWorkflowNotificationService();

        $recipients = $service->collectCurrentStepRecipients($this->applicationform(), $runId);

        $this->assertSame([
            'super-admin@example.test',
            'validateur@example.test',
        ], $this->emails($recipients));
    }

    /** L’acceptation finale vise l’auteure, les responsables et les lecteurs habilités. */
    public function testCollecteLesDestinatairesDeLAcceptationFinale(): void
    {
        $this->prepareWorkflowData();
        $service = new ValidationWorkflowNotificationService();

        $recipients = $service->collectFinalAcceptanceRecipients($this->applicationform());

        $this->assertSame([
            'admin-visible@example.test',
            'auteure@example.test',
            'responsable@example.test',
            'super-admin@example.test',
        ], $this->emails($recipients));
    }

    /** Prépare une DAE avec validateurs, responsables et périmètres distincts. */
    private function prepareWorkflowData(): int
    {
        $connection = ConnectionManager::get('test');
        $timestamp = '2026-11-25 07:00:00';
        $connection->update('departments', ['deleted' => null], ['id' => 1]);
        $connection->update('roles', [
            'code' => 'adm',
            'name' => 'Administrateur',
            'sort' => 'Administrateur',
            'deleted' => null,
        ], ['id' => 1]);
        foreach (
            [
            [2, 'dem', 'Demandeur'],
            [3, 'val', 'Validateur'],
            ] as [$id, $code, $name]
        ) {
            $connection->insert('roles', [
                'id' => $id,
                'base' => 0,
                'code' => $code,
                'name' => $name,
                'sort' => $name,
                'deleted' => null,
                'created' => $timestamp,
                'modified' => $timestamp,
            ]);
        }

        $connection->update('users', [
            'email' => 'auteure@example.test',
            'role_id' => 2,
            'issuperuser' => 0,
            'deleted' => null,
        ], ['id' => 1]);
        $connection->update('users', [
            'email' => 'admin-visible@example.test',
            'deleted' => null,
        ], ['id' => 2]);
        foreach (
            [
            [3, 'validateur@example.test', 3, false],
            [4, 'VALIDATEUR@EXAMPLE.TEST', 3, false],
            [5, 'responsable@example.test', 2, false],
            [6, 'super-admin@example.test', 1, true],
            [7, 'admin-hors-perimetre@example.test', 1, false],
            [8, 'manager-field@example.test', 2, false],
            ] as [$id, $email, $roleId, $isSuperUser]
        ) {
            $connection->insert('users', [
                'id' => $id,
                'username' => 'notification-user-' . $id,
                'email' => $email,
                'password' => 'test-password',
                'firstname' => 'Utilisateur',
                'lastname' => (string)$id,
                'token' => null,
                'issuperuser' => $isSuperUser ? 1 : 0,
                'role_id' => $roleId,
                'token_expires' => null,
                'deleted' => null,
                'created' => $timestamp,
                'modified' => $timestamp,
            ]);
        }
        foreach ([[2, 2], [3, 3], [4, 4]] as [$id, $userId]) {
            $connection->insert('user_departments', [
                'id' => $id,
                'user_id' => $userId,
                'department_id' => 1,
                'created' => $timestamp,
                'modified' => $timestamp,
            ]);
        }
        foreach ([1, 5] as $userId) {
            $connection->insert('department_managers', [
                'department_id' => 1,
                'user_id' => $userId,
            ]);
        }
        $connection->update('applicationforms', [
            'deleted' => null,
            'manager_id' => 8,
        ], ['id' => 1]);
        $connection->update('validationsequences', ['deleted' => null, 'role_id' => 3], ['id' => 1]);

        $runs = TableRegistry::getTableLocator()->get('ValidationWorkflowRuns');
        $run = $runs->saveOrFail($runs->newEntity([
            'applicationform_id' => 1,
            'started_by_user_id' => 1,
            'state' => 'en_attente',
            'started_at' => $timestamp,
        ]));
        $connection->update('applicationvalidationsteps', [
            'applicationform_id' => 1,
            'validation_workflow_run_id' => $run->get('id'),
            'role_id' => 3,
            'validationsequence_id' => 1,
            'validationstatus_id' => null,
            'state' => 'en_attente',
            'activated_at' => $timestamp,
            'deleted' => null,
        ], ['id' => 1]);

        return (int)$run->get('id');
    }

    /** Charge la DAE avec ses relations de destinataires. */
    private function applicationform(): Applicationform
    {
        /** @var \App\Model\Table\ApplicationformsTable $applicationforms */
        $applicationforms = TableRegistry::getTableLocator()->get('Applicationforms');

        return $applicationforms->get(1, contain: ['Users', 'Departments' => ['Managers']]);
    }

    /** @param list<\App\Model\Entity\User> $recipients
     * @return list<string>
     */
    private function emails(array $recipients): array
    {
        $emails = array_map(
            static fn($recipient): string => strtolower((string)$recipient->get('email')),
            $recipients,
        );
        sort($emails);

        return $emails;
    }
}
