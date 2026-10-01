<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\User;
use App\Model\Entity\WorkflowSetting;
use App\Policy\WorkflowSettingPolicy;
use Cake\TestSuite\TestCase;

class PolicySettingsTest extends TestCase
{
    public function testLeParametrageEstAutoriseUniquementAuSuperUtilisateur(): void
    {
        $policy = new WorkflowSettingPolicy();
        $setting = new WorkflowSetting();

        $this->assertTrue($policy->canIndex(new User(['issuperuser' => true]), $setting));
        $this->assertTrue($policy->canManage(new User(['issuperuser' => true]), $setting));
        $this->assertFalse($policy->canIndex(new User(['issuperuser' => false]), $setting));
    }
}
