<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\User;
use App\Model\Entity\ValidationCircuitRule;
use App\Policy\ValidationCircuitRulePolicy;
use Cake\TestSuite\TestCase;

/** Vérifie le refus par défaut et la séparation des droits locaux. */
final class ValidationCircuitRulePolicyTest extends TestCase
{
    public function testLeRoleAdministrateurNeDonneAucunDroitImplicite(): void
    {
        $operator = new User(['role_id' => User::ROLE_ADMIN, 'issuperuser' => false]);
        $policy = new ValidationCircuitRulePolicy();

        $this->assertFalse($policy->canIndex($operator, new ValidationCircuitRule()));
        $this->assertFalse($policy->canManage($operator, new ValidationCircuitRule(['department_id' => 1])));
    }

    public function testLeSuperAdministrateurPeutGererLesReglesSansRoleMetier(): void
    {
        $operator = new User(['issuperuser' => true]);
        $policy = new ValidationCircuitRulePolicy();

        $this->assertTrue($policy->canIndex($operator, new ValidationCircuitRule()));
        $this->assertTrue($policy->canManage($operator, new ValidationCircuitRule(['department_id' => 1])));
    }
}
