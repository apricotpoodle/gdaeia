<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\CgrCode;
use App\Model\Entity\User;
use App\Policy\CgrCodePolicy;
use Cake\TestSuite\TestCase;

final class CgrCodePolicyTest extends TestCase
{
    public function testLeSuperAdministrateurGereUnCodeNonSysteme(): void
    {
        $policy = new CgrCodePolicy();
        $code = new CgrCode(['is_system' => false, 'active' => true]);
        $superAdministrator = new User(['issuperuser' => true]);

        $this->assertTrue($policy->canIndex($superAdministrator, $code));
        $this->assertTrue($policy->canAdd($superAdministrator, $code));
        $this->assertTrue($policy->canView($superAdministrator, $code));
        $this->assertTrue($policy->canEdit($superAdministrator, $code));
        $this->assertTrue($policy->canDeactivate($superAdministrator, $code));
        $this->assertTrue($policy->canDelete($superAdministrator, $code));
    }

    public function testUnCodeSystemeNePeutPasEtreModifie(): void
    {
        $policy = new CgrCodePolicy();
        $code = new CgrCode(['is_system' => true, 'active' => true]);
        $superAdministrator = new User(['issuperuser' => true]);

        $this->assertFalse($policy->canEdit($superAdministrator, $code));
        $this->assertFalse($policy->canDeactivate($superAdministrator, $code));
    }

    public function testUnOperateurNePeutPasGererLesCodesCgr(): void
    {
        $policy = new CgrCodePolicy();
        $code = new CgrCode(['is_system' => false, 'active' => true]);
        $operator = new User(['issuperuser' => false]);

        $this->assertFalse($policy->canIndex($operator, $code));
        $this->assertFalse($policy->canAdd($operator, $code));
        $this->assertFalse($policy->canEdit($operator, $code));
        $this->assertFalse($policy->canDeactivate($operator, $code));
    }
}
