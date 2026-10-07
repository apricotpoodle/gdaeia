<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\Contracttype;
use App\Model\Entity\User;
use App\Policy\BudgetfeaturePolicy;
use App\Policy\ContracttypePolicy;
use App\Policy\HiringreasonPolicy;
use App\Policy\PeriodPolicy;
use App\Policy\ProfessionalcategoryPolicy;
use App\Policy\WorktimePolicy;
use App\Policy\YesnoPolicy;
use Cake\TestSuite\TestCase;

/** Vérifie la politique commune aux sept nomenclatures. */
class ReferencePolicyTest extends TestCase
{
    public function testLeSuperAdministrateurGereUneReferenceNonSocle(): void
    {
        $policy = new ContracttypePolicy();
        $reference = new Contracttype(['base' => false]);
        $superAdministrator = new User(['issuperuser' => true]);

        $this->assertTrue($policy->canIndex($superAdministrator));
        $this->assertTrue($policy->canView($superAdministrator, $reference));
        $this->assertTrue($policy->canAdd($superAdministrator, $reference));
        $this->assertTrue($policy->canEdit($superAdministrator, $reference));
        $this->assertTrue($policy->canDelete($superAdministrator, $reference));
    }

    public function testLesLignesSoclesEtLesOperateursSontProteges(): void
    {
        $policy = new ContracttypePolicy();
        $baseReference = new Contracttype(['base' => true]);
        $operator = new User(['issuperuser' => false]);

        $this->assertFalse($policy->canEdit(new User(['issuperuser' => true]), $baseReference));
        $this->assertFalse($policy->canDelete(new User(['issuperuser' => true]), $baseReference));
        $this->assertFalse($policy->canIndex($operator));
        $this->assertFalse($policy->canView($operator, $baseReference));
        $this->assertFalse($policy->canAdd($operator, $baseReference));
        $this->assertFalse($policy->canEdit($operator, $baseReference));
        $this->assertFalse($policy->canDelete($operator, $baseReference));
    }

    public function testChaquePolicyConcrèteExposeLaListe(): void
    {
        $superAdministrator = new User(['issuperuser' => true]);
        $policies = [
            new ContracttypePolicy(),
            new HiringreasonPolicy(),
            new ProfessionalcategoryPolicy(),
            new WorktimePolicy(),
            new PeriodPolicy(),
            new BudgetfeaturePolicy(),
            new YesnoPolicy(),
        ];

        foreach ($policies as $policy) {
            $this->assertTrue($policy->canIndex($superAdministrator));
        }
    }
}
