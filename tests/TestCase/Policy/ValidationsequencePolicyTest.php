<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\User;
use App\Model\Entity\Validationsequence;
use App\Policy\ValidationsequencePolicy;
use Cake\TestSuite\TestCase;

class ValidationsequencePolicyTest extends TestCase
{
    public function testLaGestionEstReserveeAuSuperUtilisateur(): void
    {
        $policy = new ValidationsequencePolicy();
        $sequence = new Validationsequence();

        $this->assertTrue($policy->canIndex(new User(['issuperuser' => true])));
        $this->assertTrue($policy->canManage(new User(['issuperuser' => true]), $sequence));
        $this->assertFalse($policy->canIndex(new User(['issuperuser' => false])));
        $this->assertFalse($policy->canManage(new User(['issuperuser' => false]), $sequence));
    }
}
