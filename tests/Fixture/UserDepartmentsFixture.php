<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UserDepartmentsFixture
 */
class UserDepartmentsFixture extends TestFixture
{
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
                'user_id' => 1,
                'department_id' => 1,
                'created' => 1783067211,
                'modified' => 1783067211,
            ],
        ];
        parent::init();
    }
}
