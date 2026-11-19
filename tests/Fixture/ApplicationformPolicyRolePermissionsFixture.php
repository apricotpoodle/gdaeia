<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/** Attributions minimales utilisées par les scénarios de la Policy DAE. */
class ApplicationformPolicyRolePermissionsFixture extends TestFixture
{
    public string $table = 'role_permissions';

    public function init(): void
    {
        $this->records = [
            ['id' => 1, 'role_id' => 1, 'permission_id' => 5, 'scope_type' => 'assigned'],
            ['id' => 2, 'role_id' => 1, 'permission_id' => 5, 'scope_type' => 'self'],
            ['id' => 3, 'role_id' => 1, 'permission_id' => 6, 'scope_type' => 'assigned'],
            ['id' => 4, 'role_id' => 1, 'permission_id' => 6, 'scope_type' => 'self'],
            ['id' => 5, 'role_id' => 1, 'permission_id' => 7, 'scope_type' => 'self'],
            ['id' => 6, 'role_id' => 1, 'permission_id' => 8, 'scope_type' => 'unscoped'],
            ['id' => 7, 'role_id' => 1, 'permission_id' => 17, 'scope_type' => 'unscoped'],
            ['id' => 8, 'role_id' => 1, 'permission_id' => 18, 'scope_type' => 'unscoped'],
            ['id' => 9, 'role_id' => 1, 'permission_id' => 19, 'scope_type' => 'unscoped'],
            ['id' => 10, 'role_id' => 1, 'permission_id' => 20, 'scope_type' => 'unscoped'],
            ['id' => 11, 'role_id' => 2, 'permission_id' => 4, 'scope_type' => 'self'],
            ['id' => 12, 'role_id' => 2, 'permission_id' => 5, 'scope_type' => 'self'],
            ['id' => 13, 'role_id' => 2, 'permission_id' => 6, 'scope_type' => 'self'],
            ['id' => 14, 'role_id' => 2, 'permission_id' => 7, 'scope_type' => 'self'],
            ['id' => 15, 'role_id' => 2, 'permission_id' => 8, 'scope_type' => 'unscoped'],
            ['id' => 16, 'role_id' => 2, 'permission_id' => 17, 'scope_type' => 'unscoped'],
        ];
        parent::init();
    }
}
