<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ValidationVisasFixture
 */
class ValidationVisasFixture extends TestFixture
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
                'applicationform_id' => 1,
                'sequence' => 1,
                'role_id' => 1,
                'op_name' => 'Lorem ipsum dolor sit amet',
                'role_name' => 'Lorem ipsum dolor sit amet',
                'status_name' => 'Lorem ipsum dolor sit amet',
                'validated_at' => 1783069297,
            ],
        ];
        parent::init();
    }
}
