<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\Applicationform;
use App\Model\Entity\User;
use App\Policy\ApplicationformPolicy;
use Authorization\IdentityInterface;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;
use stdClass;

class ApplicationformPolicyTest extends TestCase
{
    protected array $fixtures = [
        'app.UserDepartments',
        'app.ValidationWorkflowRuns',
        'app.Applicationforms',
        'app.Users',
        'app.Roles',
    ];

    private ApplicationformPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new ApplicationformPolicy();
    }

    public function testLaModificationEstAutoriseeAuProprietaireEtAuSuperAdministrateur(): void
    {
        $applicationform = new Applicationform(['user_id' => 10]);

        $this->assertTrue($this->policy->canEdit(new User(['id' => 10]), $applicationform));
        $this->assertFalse($this->policy->canEdit(new User(['id' => 11, 'role_id' => User::ROLE_ADMIN]), $applicationform));
        $this->assertTrue($this->policy->canEdit(new User(['id' => 11, 'issuperuser' => true]), $applicationform));
    }

    /** Vérifie que la synthèse des validations bloquées est réservée aux administrateurs. */
    public function testLaSyntheseDesValidationsBloqueesEstReserveeAuxAdministrateurs(): void
    {
        $applicationform = new Applicationform(['id' => 42]);

        $this->assertFalse($this->policy->canViewBlockedValidations(
            $this->identity([]),
            $applicationform,
        ));
        $this->assertFalse($this->policy->canViewBlockedValidations(
            $this->identity(new User(['role_id' => User::ROLE_DEMANDEUR])),
            $applicationform,
        ));
        $this->assertTrue($this->policy->canViewBlockedValidations(
            $this->identity(new User(['role_id' => User::ROLE_ADMIN])),
            $applicationform,
        ));
        $this->assertTrue($this->policy->canViewBlockedValidations(
            $this->identity(new User(['issuperuser' => true])),
            $applicationform,
        ));
    }

    public function testLaModificationEtLaSuppressionSontRefuseesAuDemandeurEtranger(): void
    {
        $applicationform = new Applicationform(['user_id' => 10]);
        $requester = new User(['id' => 11, 'role_id' => User::ROLE_DEMANDEUR]);

        $this->assertFalse($this->policy->canEdit($requester, $applicationform));
        $this->assertFalse($this->policy->canDelete($requester, $applicationform));
    }

    public function testLesZonesDeRemunerationEtReserveesRespectentLesRoles(): void
    {
        $applicationform = new Applicationform(['user_id' => 10]);
        $requester = new User(['id' => 10, 'role_id' => User::ROLE_DEMANDEUR]);
        $rrh = new User(['id' => 11, 'role_id' => User::ROLE_2_VALIDEUR_RRH]);
        $cg = new User(['id' => 12, 'role_id' => User::ROLE_4_VALIDEUR_CG]);

        $this->assertTrue($this->policy->canViewZoneRemuneration($requester, $applicationform));
        $this->assertFalse($this->policy->canEditZoneRemuneration($requester, $applicationform));
        $this->assertTrue($this->policy->canEditZoneRemuneration($rrh, $applicationform));
        $this->assertTrue($this->policy->canViewZoneReserves($cg, $applicationform));
        $this->assertTrue($this->policy->canEditZoneReserves($cg, $applicationform));
    }

    public function testLeLancementEstRefuseSansIdentiteOuHorsDuPerimetreVisible(): void
    {
        $applicationform = new Applicationform(['user_id' => 10]);

        $this->assertFalse($this->policy->canLaunchValidation($this->identity([]), $applicationform));
        $this->assertFalse($this->policy->canLaunchValidation(
            $this->identity(new User(['id' => 11, 'role_id' => User::ROLE_DEMANDEUR])),
            $applicationform,
        ));
    }

    public function testLeVoteEstRefuseSansIdentiteOuHorsDuPerimetreVisible(): void
    {
        $applicationform = new Applicationform(['user_id' => 10]);

        $this->assertFalse($this->policy->canVoteValidation($this->identity([]), $applicationform));
        $this->assertFalse($this->policy->canVoteValidation(
            $this->identity(new User(['id' => 11, 'role_id' => User::ROLE_DEMANDEUR])),
            $applicationform,
        ));
    }

    public function testLesCapacitesDeBaseEtLesZonesGeneralesExigentUneIdentite(): void
    {
        $applicationform = new Applicationform(['user_id' => 10]);
        $user = new User(['id' => 10, 'role_id' => User::ROLE_DEMANDEUR]);
        $invalid = $this->identity([]);

        $this->assertFalse($this->policy->canIndex($invalid));
        $this->assertTrue($this->policy->canIndex($this->identity($user)));
        $this->assertFalse($this->policy->canAdd($invalid, $applicationform));
        $this->assertTrue($this->policy->canAdd($this->identity($user), $applicationform));
        $this->assertTrue($this->policy->canViewZoneAdmin($this->identity($user), $applicationform));
        $this->assertTrue($this->policy->canViewZoneContrat($this->identity($user), $applicationform));
        $this->assertTrue($this->policy->canViewZoneCommentaires($this->identity($user), $applicationform));
        $this->assertFalse($this->policy->canViewZoneAdmin($invalid, $applicationform));
        $this->assertFalse($this->policy->canViewZoneContrat($invalid, $applicationform));
        $this->assertFalse($this->policy->canViewZoneCommentaires($invalid, $applicationform));
        $this->assertFalse($this->policy->canEdit($invalid, $applicationform));
        $this->assertFalse($this->policy->canDelete($invalid, $applicationform));
        $this->assertFalse($this->policy->canViewZoneRemuneration($invalid, $applicationform));
        $this->assertFalse($this->policy->canEditZoneRemuneration($invalid, $applicationform));
        $this->assertFalse($this->policy->canViewZoneReserves($invalid, $applicationform));
        $this->assertFalse($this->policy->canEditZoneCommentaires($invalid, $applicationform));
        $this->assertTrue($this->policy->canEditZoneAdmin($this->identity($user), $applicationform));
        $this->assertTrue($this->policy->canEditZoneContrat($this->identity($user), $applicationform));
    }

    public function testLesZonesEtLaSuppressionRespectentLeProprietaireEtLeSuperUtilisateur(): void
    {
        $applicationform = new Applicationform(['user_id' => 10]);
        $owner = $this->identity(new User(['id' => 10, 'role_id' => User::ROLE_DEMANDEUR]));
        $superUser = $this->identity(new User(['id' => 11, 'issuperuser' => true]));
        $foreign = $this->identity(new User(['id' => 12, 'role_id' => User::ROLE_DEMANDEUR]));

        $this->assertTrue($this->policy->canDelete($owner, $applicationform));
        $this->assertTrue($this->policy->canDelete($superUser, $applicationform));
        $this->assertFalse($this->policy->canDelete($foreign, $applicationform));
        $this->assertTrue($this->policy->canEditZoneReserves($this->identity(new User([
            'role_id' => User::ROLE_4_VALIDEUR_CG,
        ])), $applicationform));
        $this->assertFalse($this->policy->canViewZoneReserves($foreign, $applicationform));
        $this->assertFalse($this->policy->canEditZoneRemuneration($foreign, $applicationform));
        $this->assertTrue($this->policy->canViewZoneRemuneration($owner, $applicationform));
    }

    public function testLesBranchesDeLancementAvecAttributDeWorkflowSontExplicites(): void
    {
        $applicationform = new Applicationform([
            'id' => 999999,
            'user_id' => 10,
            'validation_workflow_run' => null,
        ]);
        $owner = $this->identity(new User(['id' => 10, 'role_id' => User::ROLE_DEMANDEUR]));

        $this->assertTrue($this->policy->canLaunchValidation($owner, $applicationform));
        $applicationform->validation_workflow_run = new stdClass();
        $this->assertFalse($this->policy->canLaunchValidation($owner, $applicationform));
        $this->assertTrue($this->policy->canVoteValidation($owner, $applicationform));
    }

    /** Vérifie que l'export PDF reprend strictement le périmètre de la fiche. */
    public function testLePdfEstAutoriseExactementCommeLaConsultation(): void
    {
        $applicationform = new Applicationform(['user_id' => 10]);
        $owner = $this->identity(new User(['id' => 10, 'role_id' => User::ROLE_DEMANDEUR]));
        $foreign = $this->identity(new User(['id' => 11, 'role_id' => User::ROLE_DEMANDEUR]));

        $this->assertSame(
            $this->policy->canView($owner, $applicationform),
            $this->policy->canViewpdf($owner, $applicationform),
        );
        $this->assertFalse($this->policy->canViewpdf($foreign, $applicationform));
    }

    /** Vérifie que la duplication reprend exactement le périmètre de consultation. */
    public function testLaDuplicationEstAutoriseePourUneDemandeVisibleSeulement(): void
    {
        $applicationform = new Applicationform(['user_id' => 10]);
        $owner = $this->identity(new User(['id' => 10, 'role_id' => User::ROLE_DEMANDEUR]));
        $foreign = $this->identity(new User(['id' => 11, 'role_id' => User::ROLE_DEMANDEUR]));

        $this->assertTrue($this->policy->canDuplicate($owner, $applicationform));
        $this->assertFalse($this->policy->canDuplicate($foreign, $applicationform));
    }

    public function testLesRequetesDePerimetreEtDeWorkflowSontEvaluees(): void
    {
        $visible = new Applicationform(['id' => 1001, 'user_id' => 10, 'department_id' => 1]);
        $operator = $this->identity(new User(['id' => 1, 'role_id' => User::ROLE_DEMANDEUR]));
        $this->assertIsBool($this->policy->canView($operator, $visible));

        $withoutAttribute = new Applicationform(['id' => 1002, 'user_id' => 1]);
        $this->assertTrue($this->policy->canLaunchValidation($operator, $withoutAttribute));
        $this->assertFalse($this->policy->canResetValidation($operator, $withoutAttribute));
        $this->assertTrue($this->policy->canEdit($operator, $withoutAttribute));
    }

    public function testUneDemandeAvecWorkflowEstEvalueePendantLEdition(): void
    {
        ConnectionManager::get('test')->insert('validation_workflow_runs', [
            'applicationform_id' => 1,
            'started_by_user_id' => 1,
            'state' => 'en_attente',
            'started_at' => date('Y-m-d H:i:s'),
            'created' => date('Y-m-d H:i:s'),
            'modified' => date('Y-m-d H:i:s'),
        ]);

        $applicationform = new Applicationform(['id' => 1, 'user_id' => 2, 'department_id' => 1]);
        $validator = $this->identity(new User(['id' => 1, 'role_id' => User::ROLE_ADMIN]));

        $this->assertFalse($this->policy->canEdit($validator, $applicationform));
        $owner = $this->identity(new User(['id' => 2, 'role_id' => User::ROLE_DEMANDEUR]));
        $this->assertFalse($this->policy->canEdit($owner, $applicationform));
        $this->assertIsBool($this->policy->canResetValidation($validator, $applicationform));
    }

    /** @param User|array<never, never> $user */
    private function identity(User|array $user): IdentityInterface
    {
        $identity = $this->createStub(IdentityInterface::class);
        $identity->method('getOriginalData')->willReturn($user);

        return $identity;
    }
}
