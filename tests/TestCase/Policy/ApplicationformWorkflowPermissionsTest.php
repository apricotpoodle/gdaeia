<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\Applicationform;
use App\Model\Entity\Applicationvalidationstep;
use App\Model\Entity\User;
use App\Model\Entity\ValidationWorkflowRun;
use App\Policy\ApplicationformPolicy;
use App\Service\Security\PermissionResolver;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;

/** Vérifie les permissions de workflow sans fixtures ni migration. */
final class ApplicationformWorkflowPermissionsTest extends TestCase
{
    private int $runCount = 0;
    private bool $lastSequenceActivated = false;

    protected function setUp(): void
    {
        parent::setUp();
        $runs = $this->createStub(SelectQuery::class);
        $runs->method('select')->willReturnSelf();
        $runs->method('where')->willReturnSelf();
        $runs->method('count')->willReturnCallback(fn(): int => $this->runCount);
        $runs->method('first')->willReturnCallback(fn(): ?ValidationWorkflowRun => $this->runCount === 1
            ? new ValidationWorkflowRun(['id' => 42])
            : null);
        $this->registerQuery('ValidationWorkflowRuns', $runs);

        $steps = $this->createStub(SelectQuery::class);
        $steps->method('select')->willReturnSelf();
        $steps->method('where')->willReturnSelf();
        $steps->method('orderByDesc')->willReturnSelf();
        $steps->method('first')->willReturnCallback(fn(): Applicationvalidationstep => new Applicationvalidationstep([
            'sequence_number' => 2,
            'activated_at' => $this->lastSequenceActivated ? new DateTimeImmutable() : null,
        ]));
        $stepsTable = $this->createStub(Table::class);
        $stepsTable->method('find')->willReturn($steps);
        $stepsTable->method('exists')->willReturnCallback(fn(): bool => $this->lastSequenceActivated);
        TableRegistry::getTableLocator()->set('Applicationvalidationsteps', $stepsTable);
    }

    protected function tearDown(): void
    {
        TableRegistry::getTableLocator()->remove('ValidationWorkflowRuns');
        TableRegistry::getTableLocator()->remove('Applicationvalidationsteps');
        parent::tearDown();
    }

    /** @return array<string, array{string, string}> */
    public static function workflowActions(): array
    {
        return [
            'lancement' => ['canLaunchValidation', 'applicationforms.launch_validation'],
            'réinitialisation' => ['canResetValidation', 'applicationforms.reset_validation'],
            'relance' => ['canRemindValidation', 'applicationforms.remind_validation'],
            'archivage' => ['canArchive', 'applicationforms.archive'],
            'outrepassement' => ['canOverrideValidation', 'applicationforms.override_validation'],
        ];
    }

    #[DataProvider('workflowActions')]
    public function testChaqueActionExigeSaPermissionSansPerimetre(string $method, string $code): void
    {
        $this->runCount = $method === 'canResetValidation' ? 1 : 0;
        $administrator = new User(['id' => 10, 'role_id' => User::ROLE_ADMIN]);
        $form = new Applicationform(['id' => 42, 'user_id' => 10, 'contracttype_id' => 1]);

        $this->assertFalse((new ApplicationformPolicy(new PermissionResolver([])))->$method($administrator, $form));
        $grants = [$code => ['all']];
        if ($method === 'canArchive') {
            $grants['applicationforms.read'] = ['self'];
        }
        $this->assertFalse((new ApplicationformPolicy(new PermissionResolver($grants)))->$method($administrator, $form));
        $grants[$code] = ['unscoped'];
        $this->assertTrue((new ApplicationformPolicy(new PermissionResolver($grants)))->$method($administrator, $form));
    }

    #[DataProvider('workflowActions')]
    public function testLeSuperAdministrateurConserveChaqueActionSansAttribution(string $method, string $code): void
    {
        $this->runCount = $method === 'canResetValidation' ? 1 : 0;
        $policy = new ApplicationformPolicy(new PermissionResolver([]));

        $this->assertTrue($policy->$method(
            new User(['id' => 10, 'role_id' => User::ROLE_ADMIN, 'issuperuser' => true]),
            new Applicationform(['id' => 42, 'user_id' => 10, 'contracttype_id' => 1]),
        ));
    }

    public function testLeLancementConserveLeCreateurLaVisibiliteEtLAbsenceDeCycle(): void
    {
        $policy = new ApplicationformPolicy(new PermissionResolver([
            'applicationforms.launch_validation' => ['unscoped'],
        ]));
        $owner = new User(['id' => 10, 'role_id' => User::ROLE_DEMANDEUR]);
        $form = new Applicationform(['id' => 42, 'user_id' => 10]);

        $this->assertTrue($policy->canLaunchValidation($owner, $form));
        $this->assertFalse($policy->canLaunchValidation(new User(['id' => 11, 'role_id' => 2]), $form));
        $this->assertFalse($policy->canLaunchValidation(new User(['id' => 11, 'role_id' => User::ROLE_ADMIN]), $form));
        $this->runCount = 1;
        $this->assertFalse($policy->canLaunchValidation($owner, $form));
        $form->validation_workflow_run = new ValidationWorkflowRun();
        $this->assertFalse($policy->canLaunchValidation($owner, $form));
    }

    public function testLaReinitialisationConserveLeRoleEtLExistenceDuCycle(): void
    {
        $policy = new ApplicationformPolicy(new PermissionResolver([
            'applicationforms.reset_validation' => ['unscoped'],
        ]));
        $administrator = new User(['id' => 10, 'role_id' => User::ROLE_ADMIN]);
        $form = new Applicationform(['id' => 42, 'user_id' => 10]);

        $this->assertFalse($policy->canResetValidation($administrator, $form));
        $this->runCount = 1;
        $this->assertTrue($policy->canResetValidation($administrator, $form));
        $this->lastSequenceActivated = true;
        $this->assertFalse($policy->canResetValidation($administrator, $form));
        $this->assertFalse($policy->canResetValidation(new User(['id' => 10, 'role_id' => 2]), $form));
    }

    public function testLaRelanceEtLOverrideConserventLeRoleEtLaVisibilite(): void
    {
        $policy = new ApplicationformPolicy(new PermissionResolver([
            'applicationforms.remind_validation' => ['unscoped'],
            'applicationforms.override_validation' => ['unscoped'],
        ]));
        $form = new Applicationform(['id' => 42, 'user_id' => 10]);
        foreach (['canRemindValidation', 'canOverrideValidation'] as $method) {
            $this->assertTrue($policy->$method(new User(['id' => 10, 'role_id' => User::ROLE_ADMIN]), $form));
            $this->assertFalse($policy->$method(new User(['id' => 10, 'role_id' => 2]), $form));
            $this->assertFalse($policy->$method(new User(['id' => 11, 'role_id' => User::ROLE_ADMIN]), $form));
        }
    }

    public function testLArchivageSuitLesDroitsHabituelsDeLectureEtAccepteToutContrat(): void
    {
        $policy = new ApplicationformPolicy(new PermissionResolver([
            'applicationforms.archive' => ['unscoped'],
            'applicationforms.read' => ['all'],
        ]));
        $form = new Applicationform(['id' => 42, 'user_id' => 12, 'contracttype_id' => 1]);
        $administrator = new User(['id' => 10, 'role_id' => User::ROLE_ADMIN]);

        $this->assertTrue($policy->canArchive($administrator, $form));
        $this->assertFalse($policy->canArchive(new User(['id' => 10, 'role_id' => User::ROLE_5_VALIDEUR_DIR]), $form));
        $this->assertFalse($policy->canArchive(new User(['id' => 10, 'role_id' => 2]), $form));
    }

    public function testLArchivageEstRefuseSansDroitDeLectureOuPendantUnCycleActif(): void
    {
        $form = new Applicationform(['id' => 42, 'user_id' => 10, 'contracttype_id' => 1]);
        $administrator = new User(['id' => 10, 'role_id' => User::ROLE_ADMIN]);
        $archiveOnly = new ApplicationformPolicy(new PermissionResolver([
            'applicationforms.archive' => ['unscoped'],
        ]));
        $archiveAndRead = new ApplicationformPolicy(new PermissionResolver([
            'applicationforms.archive' => ['unscoped'],
            'applicationforms.read' => ['self'],
        ]));

        $this->runCount = 0;
        $this->assertFalse($archiveOnly->canArchive($administrator, $form));

        $this->runCount = 1;
        $this->assertFalse($archiveAndRead->canArchive($administrator, $form));
    }

    public function testUneDaeArchiveeEstEnLectureSeuleMaisPeutEtreRestauree(): void
    {
        $policy = new ApplicationformPolicy(new PermissionResolver([
            'applicationforms.archive' => ['unscoped'],
            'applicationforms.read' => ['self'],
        ]));
        $form = new Applicationform([
            'id' => 42,
            'user_id' => 10,
            'archived' => '2026-01-01 10:00:00',
        ]);
        $administrator = new User(['id' => 10, 'role_id' => User::ROLE_ADMIN]);

        $this->assertTrue($policy->canView($administrator, $form));
        $this->assertFalse($policy->canEdit($administrator, $form));
        $this->assertTrue($policy->canRestore($administrator, $form));
        $this->assertFalse($policy->canRestore(
            new User(['id' => 11, 'role_id' => User::ROLE_ADMIN]),
            $form,
        ));
    }

    private function registerQuery(string $alias, SelectQuery $query): void
    {
        $table = $this->createStub(Table::class);
        $table->method('find')->willReturn($query);
        TableRegistry::getTableLocator()->set($alias, $table);
    }
}
