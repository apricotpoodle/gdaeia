<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\User;
use App\Policy\AppPolicy;
use Authorization\IdentityInterface;
use Cake\TestSuite\TestCase;

class AppPolicyTest extends TestCase
{
    public function testLesHelpersGereUneIdentiteValideOuInvalide(): void
    {
        $policy = new class extends AppPolicy {
            public function valid(IdentityInterface $identity): ?User
            {
                return $this->getValidUser($identity);
            }

            public function super(IdentityInterface $identity): bool
            {
                return $this->isSuperUser($identity);
            }
        };
        $identity = $this->identity(new User(['issuperuser' => true]));
        $invalid = $this->identity([]);

        $this->assertInstanceOf(User::class, $policy->valid($identity));
        $this->assertNull($policy->valid($invalid));
        $this->assertTrue($policy->super($identity));
        $this->assertFalse($policy->super($invalid));
    }

    /** @param mixed $value */
    private function identity(mixed $value): IdentityInterface
    {
        $identity = $this->createStub(IdentityInterface::class);
        $identity->method('getOriginalData')->willReturn($value);

        return $identity;
    }
}
