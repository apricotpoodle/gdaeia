<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Model\Entity\User;
use App\Service\UserRoleAuthorizationService;
use Cake\Http\Exception\ForbiddenException;
use Cake\TestSuite\TestCase;

final class UserRoleAuthorizationServiceTest extends TestCase
{
    private UserRoleAuthorizationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UserRoleAuthorizationService();
    }

    public function testUnAdministrateurLocalPeutAttribuerUnRoleFonctionnel(): void
    {
        $operator = new User(['issuperuser' => false, 'role_id' => User::ROLE_ADMIN]);
        $before = new User(['id' => 2, 'role_id' => User::ROLE_DEMANDEUR, 'issuperuser' => false]);
        $after = new User(['id' => 2, 'role_id' => User::ROLE_1_VALIDEUR_POLE, 'issuperuser' => false]);

        $this->service->assertMutationAllowed($operator, $before, $after);

        $this->addToAssertionCount(1);
    }

    public function testUnAdministrateurLocalNePeutPasAttribuerLeRoleAdmin(): void
    {
        $operator = new User(['issuperuser' => false, 'role_id' => User::ROLE_ADMIN]);
        $before = new User(['id' => 2, 'role_id' => User::ROLE_DEMANDEUR, 'issuperuser' => false]);
        $after = new User(['id' => 2, 'role_id' => User::ROLE_ADMIN, 'issuperuser' => false]);

        $this->expectException(ForbiddenException::class);
        $this->service->assertMutationAllowed($operator, $before, $after);
    }

    public function testUnAdministrateurLocalNePeutPasActiverLeStatutSuperuser(): void
    {
        $operator = new User(['issuperuser' => false, 'role_id' => User::ROLE_ADMIN]);
        $before = new User(['id' => 2, 'role_id' => User::ROLE_DEMANDEUR, 'issuperuser' => false]);
        $after = new User(['id' => 2, 'role_id' => User::ROLE_DEMANDEUR, 'issuperuser' => true]);

        $this->expectException(ForbiddenException::class);
        $this->service->assertMutationAllowed($operator, $before, $after);
    }

    public function testUnSuperuserPeutModifierLeRoleEtLeStatutSuperuser(): void
    {
        $operator = new User(['issuperuser' => true, 'role_id' => User::ROLE_ADMIN]);
        $before = new User(['id' => 2, 'role_id' => User::ROLE_DEMANDEUR, 'issuperuser' => false]);
        $after = new User(['id' => 2, 'role_id' => User::ROLE_ADMIN, 'issuperuser' => true]);

        $this->service->assertMutationAllowed($operator, $before, $after);

        $this->addToAssertionCount(1);
    }
}
