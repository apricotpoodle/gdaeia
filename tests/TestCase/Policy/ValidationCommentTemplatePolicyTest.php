<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\User;
use App\Model\Entity\ValidationCommentTemplate;
use App\Policy\ValidationCommentTemplatePolicy;
use Cake\TestSuite\TestCase;

class ValidationCommentTemplatePolicyTest extends TestCase
{
    public function testToutesLesActionsDeleguentALaLectureSuperUtilisateur(): void
    {
        $policy = new ValidationCommentTemplatePolicy();
        $template = new ValidationCommentTemplate();

        $this->assertTrue($policy->canIndex(new User(['issuperuser' => true]), $template));
        $this->assertTrue($policy->canView(new User(['issuperuser' => true]), $template));
        $this->assertTrue($policy->canAdd(new User(['issuperuser' => true]), $template));
        $this->assertTrue($policy->canEdit(new User(['issuperuser' => true]), $template));
        $this->assertTrue($policy->canDelete(new User(['issuperuser' => true]), $template));
        $this->assertFalse($policy->canIndex(new User(['issuperuser' => false]), $template));
    }
}
