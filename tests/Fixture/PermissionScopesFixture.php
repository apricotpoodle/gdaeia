<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/** Périmètres admissibles utilisés par les tests d’habilitation. */
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
        ];
        parent::init();
    }
}
