<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/** Périmètres admissibles de la matrice complète, sans attribution implicite. */
class PermissionScopesFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            ['id' => 1, 'permission_id' => 1, 'scope_type' => 'assigned'],
            ['id' => 2, 'permission_id' => 1, 'scope_type' => 'all'],
            ['id' => 3, 'permission_id' => 2, 'scope_type' => 'assigned'],
            ['id' => 4, 'permission_id' => 2, 'scope_type' => 'all'],
            ['id' => 5, 'permission_id' => 3, 'scope_type' => 'unscoped'],
            ['id' => 6, 'permission_id' => 4, 'scope_type' => 'self'],
            ['id' => 7, 'permission_id' => 5, 'scope_type' => 'self'],
            ['id' => 8, 'permission_id' => 5, 'scope_type' => 'assigned'],
            ['id' => 9, 'permission_id' => 5, 'scope_type' => 'all'],
            ['id' => 10, 'permission_id' => 6, 'scope_type' => 'self'],
            ['id' => 11, 'permission_id' => 6, 'scope_type' => 'assigned'],
            ['id' => 12, 'permission_id' => 6, 'scope_type' => 'all'],
            ['id' => 13, 'permission_id' => 7, 'scope_type' => 'self'],
            ['id' => 14, 'permission_id' => 8, 'scope_type' => 'unscoped'],
            ['id' => 15, 'permission_id' => 9, 'scope_type' => 'self'],
            ['id' => 16, 'permission_id' => 9, 'scope_type' => 'assigned'],
            ['id' => 17, 'permission_id' => 9, 'scope_type' => 'all'],
            ['id' => 18, 'permission_id' => 10, 'scope_type' => 'self'],
            ['id' => 19, 'permission_id' => 10, 'scope_type' => 'assigned'],
            ['id' => 20, 'permission_id' => 11, 'scope_type' => 'assigned'],
            ['id' => 21, 'permission_id' => 11, 'scope_type' => 'all'],
            ['id' => 22, 'permission_id' => 12, 'scope_type' => 'unscoped'],
            ['id' => 23, 'permission_id' => 13, 'scope_type' => 'unscoped'],
            ['id' => 24, 'permission_id' => 14, 'scope_type' => 'unscoped'],
            ['id' => 25, 'permission_id' => 15, 'scope_type' => 'assigned'],
            ['id' => 26, 'permission_id' => 16, 'scope_type' => 'unscoped'],
            ['id' => 27, 'permission_id' => 17, 'scope_type' => 'unscoped'],
            ['id' => 28, 'permission_id' => 18, 'scope_type' => 'unscoped'],
            ['id' => 29, 'permission_id' => 19, 'scope_type' => 'unscoped'],
            ['id' => 30, 'permission_id' => 20, 'scope_type' => 'unscoped'],
            ['id' => 31, 'permission_id' => 21, 'scope_type' => 'unscoped'],
            ['id' => 32, 'permission_id' => 22, 'scope_type' => 'unscoped'],
            ['id' => 33, 'permission_id' => 23, 'scope_type' => 'unscoped'],
            ['id' => 34, 'permission_id' => 24, 'scope_type' => 'unscoped'],
            ['id' => 35, 'permission_id' => 25, 'scope_type' => 'unscoped'],
            ['id' => 36, 'permission_id' => 26, 'scope_type' => 'unscoped'],
            ['id' => 37, 'permission_id' => 27, 'scope_type' => 'unscoped'],
            ['id' => 38, 'permission_id' => 28, 'scope_type' => 'unscoped'],
            ['id' => 39, 'permission_id' => 29, 'scope_type' => 'unscoped'],
            ['id' => 40, 'permission_id' => 30, 'scope_type' => 'unscoped'],
            ['id' => 41, 'permission_id' => 31, 'scope_type' => 'unscoped'],
            ['id' => 42, 'permission_id' => 32, 'scope_type' => 'unscoped'],
            ['id' => 43, 'permission_id' => 33, 'scope_type' => 'unscoped'],
            ['id' => 44, 'permission_id' => 34, 'scope_type' => 'unscoped'],
            ['id' => 45, 'permission_id' => 35, 'scope_type' => 'unscoped'],
            ['id' => 46, 'permission_id' => 36, 'scope_type' => 'unscoped'],
            ['id' => 47, 'permission_id' => 37, 'scope_type' => 'unscoped'],
            ['id' => 48, 'permission_id' => 38, 'scope_type' => 'unscoped'],
            ['id' => 49, 'permission_id' => 39, 'scope_type' => 'unscoped'],
            ['id' => 50, 'permission_id' => 40, 'scope_type' => 'assigned'],
            ['id' => 51, 'permission_id' => 41, 'scope_type' => 'assigned'],
            ['id' => 52, 'permission_id' => 42, 'scope_type' => 'assigned'],
        ];
        parent::init();
    }
}
