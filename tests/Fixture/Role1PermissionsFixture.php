<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/** Attributions du rôle Admin pour les tests qui ne chargent que ce rôle. */
class Role1PermissionsFixture extends TestFixture
{
    public string $table = 'role_permissions';

    public function init(): void
    {
        $this->records = [
            ['id' => 1, 'role_id' => 1, 'permission_id' => 1, 'scope_type' => 'assigned'],
            ['id' => 2, 'role_id' => 1, 'permission_id' => 2, 'scope_type' => 'assigned'],
            ['id' => 3, 'role_id' => 1, 'permission_id' => 4, 'scope_type' => 'self'],
            ['id' => 5, 'role_id' => 1, 'permission_id' => 5, 'scope_type' => 'assigned'],
            ['id' => 6, 'role_id' => 1, 'permission_id' => 5, 'scope_type' => 'self'],
            ['id' => 18, 'role_id' => 1, 'permission_id' => 6, 'scope_type' => 'assigned'],
            ['id' => 19, 'role_id' => 1, 'permission_id' => 6, 'scope_type' => 'self'],
            ['id' => 31, 'role_id' => 1, 'permission_id' => 7, 'scope_type' => 'self'],
            ['id' => 38, 'role_id' => 1, 'permission_id' => 8, 'scope_type' => 'unscoped'],
            ['id' => 45, 'role_id' => 1, 'permission_id' => 9, 'scope_type' => 'assigned'],
            ['id' => 46, 'role_id' => 1, 'permission_id' => 9, 'scope_type' => 'self'],
            ['id' => 53, 'role_id' => 1, 'permission_id' => 10, 'scope_type' => 'assigned'],
            ['id' => 54, 'role_id' => 1, 'permission_id' => 10, 'scope_type' => 'self'],
            ['id' => 61, 'role_id' => 1, 'permission_id' => 12, 'scope_type' => 'unscoped'],
            ['id' => 62, 'role_id' => 1, 'permission_id' => 13, 'scope_type' => 'unscoped'],
            ['id' => 63, 'role_id' => 1, 'permission_id' => 14, 'scope_type' => 'unscoped'],
            ['id' => 64, 'role_id' => 1, 'permission_id' => 15, 'scope_type' => 'assigned'],
            ['id' => 65, 'role_id' => 1, 'permission_id' => 17, 'scope_type' => 'unscoped'],
            ['id' => 72, 'role_id' => 1, 'permission_id' => 18, 'scope_type' => 'unscoped'],
            ['id' => 73, 'role_id' => 1, 'permission_id' => 19, 'scope_type' => 'unscoped'],
            ['id' => 74, 'role_id' => 1, 'permission_id' => 20, 'scope_type' => 'unscoped'],
            ['id' => 75, 'role_id' => 1, 'permission_id' => 21, 'scope_type' => 'unscoped'],
        ];
        parent::init();
    }
}
