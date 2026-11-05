<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Security;

use App\Model\Entity\User;
use App\Service\Security\PermissionResolver;
use Cake\TestSuite\TestCase;

final class PermissionResolverTest extends TestCase
{
    public function testUnUtilisateurSansAttributionEstRefuseParDefaut(): void
    {
        $resolver = new PermissionResolver(['departments.manage' => []]);
        $user = new User(['id' => 2, 'role_id' => 2, 'issuperuser' => false]);

        self::assertFalse($resolver->allows($user, 'departments.manage'));
    }

    public function testUnSuperAdministrateurContourneLesPermissionsOrganisationnelles(): void
    {
        $resolver = new PermissionResolver();
        $user = new User(['id' => 1, 'role_id' => 1, 'issuperuser' => true]);

        self::assertTrue($resolver->allows($user, 'departments.manage'));
    }
}
