<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\User;
use App\Model\Table\FieldAuthorizationsTable;
use App\Policy\FieldAuthorizationsTablePolicy;
use Authorization\IdentityInterface;
use Cake\TestSuite\TestCase;

class FieldAuthorizationsTablePolicyTest extends TestCase
{
    public function testLaListeEstRefuseeAuxAdministrateursEtAccordeeAuxAutresRoles(): void
    {
        $policy = new FieldAuthorizationsTablePolicy();
        $table = new FieldAuthorizationsTable();

        $this->assertFalse($policy->canIndex($this->identity(new User(['role_id' => User::ROLE_ADMIN])), $table));
        $this->assertTrue($policy->canIndex($this->identity(new User(['role_id' => User::ROLE_DEMANDEUR])), $table));
        $this->assertFalse($policy->canIndex($this->identity([]), $table));
    }

    /** @param mixed $value */
    private function identity(mixed $value): IdentityInterface
    {
        $identity = $this->createStub(IdentityInterface::class);
        $identity->method('getOriginalData')->willReturn($value);

        return $identity;
    }
}
