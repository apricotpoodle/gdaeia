<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * RoleMenusFixture
 */
class RoleMenusFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'role_menus';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'role_id' => 1,
                'menu_id' => 1,
                'department_id' => 1,
                'created' => 1783067210,
                'modified' => 1783067210,
            ],
        ];
        parent::init();
    }
}
