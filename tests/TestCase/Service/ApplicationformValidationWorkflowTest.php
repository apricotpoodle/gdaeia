<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Model\Entity\Applicationform;
use App\Model\Entity\Applicationvalidationstep;
use App\Model\Entity\Department;
use App\Model\Entity\Role;
use App\Model\Entity\User;
use App\Model\Entity\ValidationWorkflowRun;
use App\Model\Table\ValidationsequencesTable;
use App\Service\Security\PermissionResolver;
use App\Service\Workflow\ApplicationformValidationWorkflow;
use Cake\Database\Connection;
use Cake\I18n\Date;
use Cake\I18n\DateTime;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\ResultSet;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use RuntimeException;

/** Tests unitaires des règles de workflow sans accès à MySQL. */
class ApplicationformValidationWorkflowTest extends TestCase
{
    private const WORKFLOW_TABLES = [
        'ValidationWorkflowRuns', 'Applicationvalidationsteps', 'Validationsequences',
        'Roles', 'Users', 'Validations', 'WorkflowSettings', 'Applicationforms',
        'ValidationCircuitRules', 'Departments', 'Contracttypes',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        foreach (self::WORKFLOW_TABLES as $alias) {
            TableRegistry::getTableLocator()->set($alias, $this->createStub(Table::class));
        }
        $settings = $this->createStub(SelectQuery::class);
        $settings->method('where')->willReturnSelf();
        $settings->method('first')->willReturn(null);
        TableRegistry::getTableLocator()->get('WorkflowSettings')->method('find')->willReturn($settings);
    }

    protected function tearDown(): void
    {
        foreach (self::WORKFLOW_TABLES as $alias) {
            TableRegistry::getTableLocator()->remove($alias);
        }
        parent::tearDown();
    }

    public function testLeLancementSignaleLAbsenceDeSequenceDeValidation(): void
    {
        $query = $this->createMock(SelectQuery::class);
        $query->expects($this->once())->method('contain')->with(['Roles'])->willReturnSelf();
        $query->expects($this->once())->method('where')->with([
            'Validationsequences.department_id' => 8,
            'Validationsequences.deleted IS' => null,
        ])->willReturnSelf();
        $query->expects($this->once())->method('orderByAsc')
            ->with('Validationsequences.sequence')
            ->willReturnSelf();
        $query->expects($this->once())->method('all')->willReturn(new ResultSet([]));

        $sequences = $this->createMock(ValidationsequencesTable::class);
        $sequences->expects($this->once())->method('find')->willReturn($query);
        TableRegistry::getTableLocator()->remove('Validationsequences');
        TableRegistry::getTableLocator()->set('Validationsequences', $sequences);

        $result = (new ApplicationformValidationWorkflow())->start(
            new Applicationform(['department_id' => 8]),
            new User(['id' => 12]),
        );

        $this->assertSame([
            'Aucune séquence de validation n’est configurée pour ce département.',
        ], $result['issues']);
        $this->assertSame([], $result['recipients']);
        $this->assertNull($result['run']);
    }

    public function testUnAdministrateurPeutOutrepasserUneEtapeActiveOuBloquee(): void
    {
        $workflow = new ApplicationformValidationWorkflow(new PermissionResolver([
            'applicationforms.override_validation' => ['unscoped'],
        ]));
        $applicationform = new Applicationform(['department_id' => 8]);
        $expiredStep = new Applicationvalidationstep([
            'role_id' => 3,
            'state' => 'en_attente',
            'due_at' => DateTime::now()->subSeconds(1),
        ]);
        $futureStep = new Applicationvalidationstep([
            'role_id' => 3,
            'state' => 'a_venir',
            'due_at' => DateTime::now()->addSeconds(1),
        ]);

        $this->assertTrue($workflow->canVoteStep(
            $expiredStep,
            $applicationform,
            new User(['role_id' => User::ROLE_ADMIN]),
            true,
        ));
        $this->assertTrue($workflow->canVoteStep(
            $futureStep,
            $applicationform,
            new User(['role_id' => User::ROLE_ADMIN]),
            true,
        ));
        $this->assertFalse($workflow->canVoteStep(
            new Applicationvalidationstep(['role_id' => 3, 'state' => 'a_venir']),
            $applicationform,
            new User(['role_id' => User::ROLE_ADMIN]),
            false,
        ));
        $this->assertFalse($workflow->canVoteStep(
            $expiredStep,
            $applicationform,
            new User(['role_id' => User::ROLE_DEMANDEUR]),
            true,
        ));
    }

    public function testLOverrideEtLeVoteParProcurationExigentLaPermissionDediee(): void
    {
        $administrator = new User(['id' => 10, 'role_id' => User::ROLE_ADMIN]);
        $form = new Applicationform(['id' => 42, 'user_id' => 10]);
        $step = new Applicationvalidationstep(['role_id' => 3, 'state' => 'en_attente']);

        foreach ([[], ['applicationforms.override_validation' => ['all']]] as $grants) {
            $workflow = new ApplicationformValidationWorkflow(new PermissionResolver($grants));
            $this->assertFalse($workflow->canOverrideStep($step, $administrator));
            $this->assertFalse($workflow->canVoteStep($step, $form, $administrator, true));
        }
    }

    public function testLOverrideConserveLesEtatsEtLeSuperAdministrateur(): void
    {
        $workflow = new ApplicationformValidationWorkflow(new PermissionResolver([]));
        $superUser = new User(['id' => 10, 'issuperuser' => true]);

        $this->assertTrue($workflow->canOverrideStep(new Applicationvalidationstep(['state' => 'en_attente']), $superUser));
        $this->assertTrue($workflow->canOverrideStep(new Applicationvalidationstep(['state' => 'a_venir']), $superUser));
        foreach (['acceptee', 'refusee', 'annulee'] as $state) {
            $this->assertFalse($workflow->canOverrideStep(new Applicationvalidationstep(['state' => $state]), $superUser));
        }
    }

    public function testLaCollecteDesRelancesNeModifiePasLEtapeAvantLeResultatDesEnvois(): void
    {
        $applicationform = new Applicationform(['id' => 42, 'department_id' => 8]);
        $step = new Applicationvalidationstep([
            'id' => 7,
            'applicationform_id' => 42,
            'role_id' => 3,
            'state' => 'en_attente',
            'due_at' => DateTime::now()->subSeconds(1),
            'reminder_count' => 2,
            'last_reminded_at' => null,
            'applicationform' => $applicationform,
        ]);
        $stepsQuery = $this->createStub(SelectQuery::class);
        $stepsQuery->method('contain')->willReturnSelf();
        $stepsQuery->method('where')->willReturnSelf();
        $stepsQuery->method('all')->willReturn(new ResultSet([$step]));
        $steps = $this->createStub(Table::class);
        $steps->method('find')->willReturn($stepsQuery);

        $roleQuery = $this->createStub(SelectQuery::class);
        $roleQuery->method('select')->willReturnSelf();
        $roleQuery->method('where')->willReturnSelf();
        $roleQuery->method('first')->willReturn(new Role(['id' => 3, 'code' => 'val']));
        $roles = $this->createStub(Table::class);
        $roles->method('find')->willReturn($roleQuery);

        $recipient = new User(['id' => 11, 'email' => 'validateur@example.test']);
        $usersQuery = $this->createStub(SelectQuery::class);
        $usersQuery->method('innerJoinWith')->willReturnSelf();
        $usersQuery->method('where')->willReturnSelf();
        $usersQuery->method('all')->willReturn(new ResultSet([$recipient]));
        $users = $this->createStub(Table::class);
        $users->method('find')->willReturn($usersQuery);

        foreach (['Applicationvalidationsteps' => $steps, 'Roles' => $roles, 'Users' => $users] as $alias => $table) {
            TableRegistry::getTableLocator()->remove($alias);
            TableRegistry::getTableLocator()->set($alias, $table);
        }

        $reminders = (new ApplicationformValidationWorkflow())->collectDueReminders();

        $this->assertSame([[
            'step_id' => 7,
            'reminder_count' => 2,
            'applicationform' => $applicationform,
            'recipients' => [$recipient],
        ]], $reminders);
        $this->assertNull($step->get('last_reminded_at'));
        $this->assertSame(2, $step->get('reminder_count'));
    }

    public function testLEtatDeRelanceNestAvanceQuApresSuccesConfirme(): void
    {
        $steps = $this->createMock(Table::class);
        $steps->expects($this->once())->method('updateAll')->with(
            $this->callback(function (array $fields): bool {
                $this->assertInstanceOf(DateTime::class, $fields['last_reminded_at']);
                $this->assertInstanceOf(DateTime::class, $fields['modified']);
                $this->assertSame(3, $fields['reminder_count']);

                return true;
            }),
            ['id' => 7, 'state' => 'en_attente', 'reminder_count' => 2],
        )->willReturn(1);
        TableRegistry::getTableLocator()->remove('Applicationvalidationsteps');
        TableRegistry::getTableLocator()->set('Applicationvalidationsteps', $steps);

        $updated = (new ApplicationformValidationWorkflow())->markReminderSent(7, 3);

        $this->assertTrue($updated);
    }

    public function testLaSyntheseAccepteeFiltreChaqueAdminSelonSonPerimetre(): void
    {
        $firstForm = new Applicationform(['id' => 10, 'department_id' => 1, 'user_id' => 101]);
        $secondForm = new Applicationform(['id' => 12, 'department_id' => 2, 'user_id' => 102]);
        $firstRun = new ValidationWorkflowRun(['id' => 1, 'state' => 'acceptee']);
        $firstRun->set('applicationform', $firstForm, ['guard' => false]);
        $secondRun = new ValidationWorkflowRun(['id' => 2, 'state' => 'acceptee']);
        $secondRun->set('applicationform', $secondForm, ['guard' => false]);
        $runsQuery = $this->createStub(SelectQuery::class);
        $runsQuery->method('contain')->willReturnSelf();
        $runsQuery->method('where')->willReturnSelf();
        $runsQuery->method('orderByAsc')->willReturnSelf();
        $runsQuery->method('all')->willReturn(new ResultSet([$firstRun, $secondRun]));
        $runs = $this->createStub(Table::class);
        $runs->method('find')->willReturn($runsQuery);

        $firstAdmin = new User(['id' => 1, 'role_id' => User::ROLE_ADMIN, 'email' => 'first@example.test']);
        $secondAdmin = new User(['id' => 2, 'role_id' => User::ROLE_ADMIN, 'email' => 'second@example.test']);
        $outsideAdmin = new User(['id' => 3, 'role_id' => User::ROLE_ADMIN, 'email' => 'outside@example.test']);
        $superuser = new User(['id' => 4, 'issuperuser' => true, 'email' => 'super@example.test']);
        $usersQuery = $this->createStub(SelectQuery::class);
        $usersQuery->method('where')->willReturnSelf();
        $usersQuery->method('all')->willReturn(new ResultSet([
            $firstAdmin,
            $secondAdmin,
            $outsideAdmin,
            $superuser,
        ]));
        $users = $this->createStub(Table::class);
        $users->method('find')->willReturn($usersQuery);

        $allowed = [1 => [1], 2 => [2]];
        $userDepartments = $this->createStub(Table::class);
        $userDepartments->method('find')->willReturnCallback(function () use ($allowed): SelectQuery {
            $conditions = [];
            $query = $this->createStub(SelectQuery::class);
            $query->method('where')->willReturnCallback(
                static function (array $where) use (&$conditions, $query): SelectQuery {
                    $conditions = array_merge($conditions, $where);

                    return $query;
                },
            );
            $query->method('count')->willReturnCallback(static function () use (&$conditions, $allowed): int {
                $userId = (int)($conditions['user_id'] ?? 0);
                $departmentId = (int)($conditions['department_id'] ?? 0);

                return in_array($departmentId, $allowed[$userId] ?? [], true) ? 1 : 0;
            });

            return $query;
        });

        foreach (
            [
            'ValidationWorkflowRuns' => $runs,
            'Users' => $users,
            'UserDepartments' => $userDepartments,
            ] as $alias => $table
        ) {
            TableRegistry::getTableLocator()->remove($alias);
            TableRegistry::getTableLocator()->set($alias, $table);
        }
        $from = new DateTime('2026-11-23 00:00:00');
        $to = new DateTime('2026-11-30 00:00:00');
        $workflow = new ApplicationformValidationWorkflow(new PermissionResolver([
            'applicationforms.read' => [PermissionResolver::SCOPE_ASSIGNED],
        ]));

        $digests = $workflow->collectAcceptedDigest($from, $to);

        $byEmail = [];
        foreach ($digests as $digest) {
            $byEmail[$digest['recipient']->email] = array_map(
                static fn(Applicationform $form): int => (int)$form->id,
                $digest['applicationforms'],
            );
        }
        $this->assertSame([
            'first@example.test' => [10],
            'second@example.test' => [12],
            'super@example.test' => [10, 12],
        ], $byEmail);
    }

    public function testLaSyntheseEnAttenteDedoublonneLesAdressesEtLesDemandes(): void
    {
        $firstForm = new Applicationform(['id' => 10, 'department_id' => 1, 'begin_at' => new DateTime('2026-11-20')]);
        $secondForm = new Applicationform(['id' => 12, 'department_id' => 1, 'begin_at' => new DateTime('2026-11-21')]);
        $stepsData = [];
        foreach ([[$firstForm, 7], [$secondForm, 8], [$firstForm, 9]] as [$form, $id]) {
            $stepsData[] = new Applicationvalidationstep([
                'id' => $id,
                'role_id' => 3,
                'state' => 'en_attente',
                'applicationform' => $form,
            ]);
        }
        $stepsQuery = $this->createStub(SelectQuery::class);
        $stepsQuery->method('contain')->willReturnSelf();
        $stepsQuery->method('where')->willReturnSelf();
        $stepsQuery->method('orderByAsc')->willReturnSelf();
        $stepsQuery->method('all')->willReturn(new ResultSet($stepsData));
        $steps = $this->createStub(Table::class);
        $steps->method('find')->willReturn($stepsQuery);

        $roleQuery = $this->createStub(SelectQuery::class);
        $roleQuery->method('select')->willReturnSelf();
        $roleQuery->method('where')->willReturnSelf();
        $roleQuery->method('first')->willReturn(new Role(['id' => 3, 'code' => 'val']));
        $roles = $this->createStub(Table::class);
        $roles->method('find')->willReturn($roleQuery);

        $recipient = new User(['id' => 11, 'email' => ' Valideur@Example.Test ']);
        $duplicate = new User(['id' => 12, 'email' => 'valideur@example.test']);
        $usersQuery = $this->createStub(SelectQuery::class);
        $usersQuery->method('innerJoinWith')->willReturnSelf();
        $usersQuery->method('where')->willReturnSelf();
        $usersQuery->method('all')->willReturn(new ResultSet([$recipient, $duplicate]));
        $users = $this->createStub(Table::class);
        $users->method('find')->willReturn($usersQuery);

        foreach (
            [
            'Applicationvalidationsteps' => $steps,
            'Roles' => $roles,
            'Users' => $users,
            ] as $alias => $table
        ) {
            TableRegistry::getTableLocator()->remove($alias);
            TableRegistry::getTableLocator()->set($alias, $table);
        }

        $digests = (new ApplicationformValidationWorkflow())->collectPendingDigest();

        $this->assertCount(1, $digests);
        $this->assertSame($recipient, $digests[0]['recipient']);
        $this->assertSame([$firstForm, $secondForm], $digests[0]['applicationforms']);
    }

    public function testLeDigestBloqueExigeLOutrepassementSansCacherLesCyclesVisibles(): void
    {
        $department = new Department(['id' => 4, 'name' => 'Ressources humaines']);
        $applicationform = new Applicationform([
            'id' => 42,
            'department_id' => 4,
            'begin_at' => new Date('2026-09-30'),
        ]);
        $applicationform->set('department', $department, ['guard' => false]);
        $step = new Applicationvalidationstep([
            'id' => 7,
            'applicationform_id' => 42,
            'role_id' => 3,
            'state' => 'en_attente',
            'activated_at' => new DateTime('2026-10-01 09:00:00'),
        ]);
        $step->set('applicationform', $applicationform, ['guard' => false]);
        $step->set('role', new Role(['id' => 3, 'name' => 'Validateur']), ['guard' => false]);
        $step->set('validation_workflow_run', new ValidationWorkflowRun([
            'id' => 1,
            'state' => ValidationWorkflowRun::STATE_PENDING,
        ]), ['guard' => false]);
        $stepsQuery = $this->createStub(SelectQuery::class);
        $stepsQuery->method('contain')->willReturnSelf();
        $stepsQuery->method('where')->willReturnSelf();
        $stepsQuery->method('orderByAsc')->willReturnSelf();
        $stepsQuery->method('all')->willReturn(new ResultSet([$step]));
        $steps = $this->createStub(Table::class);
        $steps->method('find')->willReturn($stepsQuery);
        TableRegistry::getTableLocator()->remove('Applicationvalidationsteps');
        TableRegistry::getTableLocator()->set('Applicationvalidationsteps', $steps);
        $viewer = new User(['id' => 9, 'role_id' => User::ROLE_ADMIN]);
        $grants = ['applicationforms.read' => [PermissionResolver::SCOPE_ALL]];

        $withoutOverride = new ApplicationformValidationWorkflow(new PermissionResolver($grants));
        $this->assertCount(1, $withoutOverride->findBlockedCycles($viewer));
        $this->assertSame([], $withoutOverride->findBlockedCycles($viewer, true));

        $withOverride = new ApplicationformValidationWorkflow(new PermissionResolver([
            ...$grants,
            'applicationforms.override_validation' => [PermissionResolver::SCOPE_UNSCOPED],
        ]));
        $this->assertCount(1, $withOverride->findBlockedCycles($viewer, true));
    }

    public function testLeVoteProxyReelRefuseUnAdministrateurSansLaPermission(): void
    {
        $connection = $this->createStub(Connection::class);
        $connection->method('transactional')->willReturnCallback(static fn(callable $callback): mixed => $callback());
        $step = new Applicationvalidationstep([
            'id' => 5, 'applicationform_id' => 42, 'validation_workflow_run_id' => 3,
            'role_id' => 4, 'state' => 'en_attente',
        ]);
        $run = new ValidationWorkflowRun(['id' => 3, 'applicationform_id' => 42, 'state' => 'en_attente']);
        foreach (['Applicationvalidationsteps' => $step, 'ValidationWorkflowRuns' => $run, 'Validations' => null] as $alias => $entity) {
            $query = $this->createStub(SelectQuery::class);
            $query->method('where')->willReturnSelf();
            $query->method('epilog')->willReturnSelf();
            $query->method('first')->willReturn($entity);
            $query->method('count')->willReturn(0);
            $table = $this->createMock(Table::class);
            $table->method('find')->willReturn($query);
            $table->method('getConnection')->willReturn($connection);
            $table->expects($this->never())->method('save');
            TableRegistry::getTableLocator()->remove($alias);
            TableRegistry::getTableLocator()->set($alias, $table);
        }
        $workflow = new ApplicationformValidationWorkflow(new PermissionResolver([]));
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Vous n’êtes pas autorisé à voter pour cette étape.');

        $workflow->vote(
            new Applicationform(['id' => 42, 'user_id' => 10]),
            new User(['id' => 10, 'role_id' => User::ROLE_ADMIN]),
            5,
            true,
            'Accord par procuration interdit.',
            true,
        );
    }
}
