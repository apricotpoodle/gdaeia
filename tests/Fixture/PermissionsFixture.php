<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/** Données du catalogue de permissions pour les tests. */
class PermissionsFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            ['id' => 1, 'resource' => 'departments', 'action' => 'read', 'label' => 'Consulter les départements'],
            ['id' => 2, 'resource' => 'departments', 'action' => 'manage', 'label' => 'Administrer les départements'],
            ['id' => 3, 'resource' => 'menus', 'action' => 'manage', 'label' => 'Administrer les menus'],
        ];
        parent::init();
    }
}
