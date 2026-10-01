<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\User;
use App\Policy\Trait\ImpersonationCheckTrait;
use Authorization\IdentityInterface;
use Cake\TestSuite\TestCase;

class ImpersonationCheckTraitTest extends TestCase
{
    public function testLeTraitDetecteUneUsurpationCompleteEtRetourneSonAdministrateur(): void
    {
        $helper = new class {
            use ImpersonationCheckTrait;

            public function impersonating(IdentityInterface $identity): bool
            {
                return $this->isImpersonating($identity);
            }

            public function originalAdmin(IdentityInterface $identity): int|string|null
            {
                return $this->getOriginalAdminId($identity);
            }
        };
        $identity = $this->identity(new User([
            'is_impersonating' => true,
            'original_admin_id' => 42,
        ]));

        $this->assertTrue($helper->impersonating($identity));
        $this->assertSame(42, $helper->originalAdmin($identity));
        $this->assertFalse($helper->impersonating($this->identity([])));
        $this->assertNull($helper->originalAdmin($this->identity([])));
        $this->assertFalse($helper->impersonating($this->identity(new User([
            'is_impersonating' => true,
        ]))));
    }

    /** @param mixed $value */
    private function identity(mixed $value): IdentityInterface
    {
        $identity = $this->createStub(IdentityInterface::class);
        $identity->method('getOriginalData')->willReturn($value);

        return $identity;
    }
}
