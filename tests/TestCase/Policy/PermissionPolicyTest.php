<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\Permission;
use App\Model\Entity\User;
use App\Policy\PermissionPolicy;
use App\Service\Security\PermissionResolver;
use Cake\TestSuite\TestCase;

final class PermissionPolicyTest extends TestCase
{
    public function testLeCatalogueEstReserveAuxAdministrateursHabilites(): void
    {
        $policy = new PermissionPolicy(new PermissionResolver([]));
        $permission = new Permission();

        self::assertFalse($policy->canIndex(new User(['issuperuser' => false]), $permission));
        self::assertTrue($policy->canIndex(new User(['issuperuser' => true]), $permission));
    }
}
