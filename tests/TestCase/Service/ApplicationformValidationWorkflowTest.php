<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Model\Entity\Applicationform;
use App\Model\Entity\Applicationvalidationstep;
use App\Model\Entity\User;
use App\Model\Entity\ValidationWorkflowRun;
use App\Model\Table\ValidationsequencesTable;
use App\Service\Security\PermissionResolver;
use App\Service\Workflow\ApplicationformValidationWorkflow;
use Cake\Database\Connection;
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
