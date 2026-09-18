<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Model\Entity\Applicationform;
use App\Model\Entity\Applicationvalidationstep;
use App\Model\Entity\User;
use App\Model\Table\ValidationsequencesTable;
use App\Service\Workflow\ApplicationformValidationWorkflow;
use Cake\I18n\DateTime;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\ResultSet;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/** Tests unitaires des règles de workflow sans accès à MySQL. */
class ApplicationformValidationWorkflowTest extends TestCase
{
    protected function tearDown(): void
    {
        TableRegistry::getTableLocator()->remove('Validationsequences');
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

    public function testLaSuppleanceEstReserveeAUnAdministrateurApresEcheance(): void
    {
        $workflow = new ApplicationformValidationWorkflow();
        $applicationform = new Applicationform(['department_id' => 8]);
        $expiredStep = new Applicationvalidationstep([
            'role_id' => 3,
            'due_at' => DateTime::now()->subSeconds(1),
        ]);
        $futureStep = new Applicationvalidationstep([
            'role_id' => 3,
            'due_at' => DateTime::now()->addSeconds(1),
        ]);

        $this->assertTrue($workflow->canVoteStep(
            $expiredStep,
            $applicationform,
            new User(['role_id' => User::ROLE_ADMIN]),
            true,
        ));
        $this->assertFalse($workflow->canVoteStep(
            $futureStep,
            $applicationform,
            new User(['role_id' => User::ROLE_ADMIN]),
            true,
        ));
        $this->assertFalse($workflow->canVoteStep(
            $expiredStep,
            $applicationform,
            new User(['role_id' => User::ROLE_DEMANDEUR]),
            true,
        ));
    }
}
