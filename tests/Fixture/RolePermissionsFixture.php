<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/** Attribution explicite des habilitations de l’administrateur de test. */
class RolePermissionsFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            ['id' => 1, 'role_id' => 1, 'permission_id' => 1, 'scope_type' => 'assigned'],
            ['id' => 2, 'role_id' => 1, 'permission_id' => 2, 'scope_type' => 'assigned'],
        ];
        parent::init();
    }
}
