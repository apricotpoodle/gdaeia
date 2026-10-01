<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\FieldAuthorization;
use App\Model\Entity\User;
use App\Policy\FieldAuthorizationPolicy;
use Cake\TestSuite\TestCase;

class FieldAuthorizationPolicyTest extends TestCase
{
    public function testToutesLesActionsSontReserveesAuSuperUtilisateur(): void
    {
        $policy = new FieldAuthorizationPolicy();
        $record = new FieldAuthorization();
        $superUser = new User(['issuperuser' => true]);
        $operator = new User(['issuperuser' => false]);

        $this->assertTrue($policy->canIndex($superUser));
        $this->assertTrue($policy->canView($superUser, $record));
        $this->assertTrue($policy->canAdd($superUser));
        $this->assertTrue($policy->canEdit($superUser, $record));
        $this->assertTrue($policy->canDelete($superUser, $record));
        $this->assertFalse($policy->canIndex($operator));
        $this->assertFalse($policy->canAdd($operator));
    }
}
