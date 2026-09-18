<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\Applicationform;
use App\Model\Entity\User;
use App\Policy\ApplicationformPolicy;
use Authorization\IdentityInterface;
use Cake\TestSuite\TestCase;

class ApplicationformPolicyTest extends TestCase
{
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

    /** @param User|array<never, never> $user */
    private function identity(User|array $user): IdentityInterface
    {
        $identity = $this->createStub(IdentityInterface::class);
        $identity->method('getOriginalData')->willReturn($user);

        return $identity;
    }
}
